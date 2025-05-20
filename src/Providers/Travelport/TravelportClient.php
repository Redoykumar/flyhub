<?php
namespace Redoy\FlyHub\Providers\Travelport;

use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Redoy\Flyhub\Providers\Travelport\Traits\HandlesApiErrors;


class CircuitBreakerOpenException extends RuntimeException
{
}

class TravelportClient
{
    use HandlesApiErrors;
    private const SUPPORTED_METHODS = ['get', 'post', 'put', 'delete'];
    private const REQUIRED_CONFIG_KEYS = ['username', 'password', 'client_id', 'client_secret', 'access_group'];
    private const TOKEN_CACHE_KEY = 'travelport_token';
    private const TOKEN_CACHE_TTL = 82800; // 23 hours
    private const CIRCUIT_BREAKER_KEY = 'travelport_circuit_breaker';
    private const CIRCUIT_BREAKER_STATES = ['CLOSED', 'OPEN', 'HALF_OPEN'];

    private string $baseUrl;
    private string $authUrl;
    private array $config;
    private string $method = 'post';
    private string $endpoint = '';
    private array $headers = [];
    private array $params = [];
    private ?array $body = null;

    // Circuit breaker settings (configurable via $config)
    private int $failureThreshold;
    private int $openTimeoutSeconds;
    private int $halfOpenAttempts;

    // Timeout and retry settings (configurable via $config)
    private int $connectTimeout;
    private int $requestTimeout;
    private int $maxRetries;
    private int $retryDelayMs;

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = $config;
        $env = $config['environment'] ?? 'preproduction';
        $this->baseUrl = rtrim($config['base_urls'][$env] ?? 'https://api.pp.travelport.com/11/', '/');
        $this->authUrl = $config['auth_urls'][$env] ?? 'https://oauth.pp.travelport.com/oauth/oauth20/token';

        // Circuit breaker defaults
        $this->failureThreshold = $config['circuit_breaker']['failure_threshold'] ?? 5;
        $this->openTimeoutSeconds = $config['circuit_breaker']['open_timeout_seconds'] ?? 60;
        $this->halfOpenAttempts = $config['circuit_breaker']['half_open_attempts'] ?? 1;

        // Timeout and retry defaults
        $this->connectTimeout = $config['timeout']['connect'] ?? 5; // seconds
        $this->requestTimeout = $config['timeout']['request'] ?? 15; // seconds
        $this->maxRetries = $config['retry']['max_attempts'] ?? 3;
        $this->retryDelayMs = $config['retry']['delay_ms'] ?? 100;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers + $this->headers;
        return $this;
    }

    public function withParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function request(string $method, string $endpoint): self
    {
        $method = strtolower($method);
        if (!in_array($method, self::SUPPORTED_METHODS, true)) {
            throw new InvalidArgumentException("Unsupported HTTP method: {$method}");
        }
        if (empty($endpoint)) {
            throw new InvalidArgumentException('Endpoint cannot be empty');
        }

        $this->method = $method;
        $this->endpoint = ltrim($endpoint, '/');
        $this->body = null;
        return $this;
    }

    public function withBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function send(): Response
    {

        
        $url = "{$this->baseUrl}/{$this->endpoint}";
        $headers = $this->buildHeaders();
        $http = $this->buildHttpClient($headers);

        // $response = Http::withHeaders($headers)->withBody()->post($url);
        // dd($response->body());
        $this->logRequest($url, $headers);

        return $this->safeExecute(function () use ($http, $url) {
            $response = $this->executeRequestWithRetries($http, $url);
            $this->updateCircuitBreaker($response->successful());
            $this->logResponse($url, $response);
            return $response;
        });
    }

    private function validateConfig(array $config): void
    {
        foreach (self::REQUIRED_CONFIG_KEYS as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException("Missing required configuration key: {$key}");
            }
        }
    }

    private function getToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_CACHE_TTL, function () {
            $response = Http::asForm()
                ->timeout($this->requestTimeout)
                ->connectTimeout($this->connectTimeout)
                ->post($this->authUrl, [
                    'grant_type' => 'password',
                    'username' => $this->config['username'],
                    'password' => $this->config['password'],
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'scope' => 'openid',
                ]);

            if (!$response->successful()) {
                throw new RuntimeException("OAuth failed: {$response->body()}");
            }

            $token = $response->json('access_token');
            if (!$token) {
                throw new RuntimeException('Access token not found');
            }

            return $token;
        });
    }

    private function buildHeaders(): array
    {
        static $defaultHeaders = null;
        if ($defaultHeaders === null) {
            $defaultHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate', // Add Accept-Encoding header
                'X-API-Version' => '11',
            ];
        }

        return [
            'Authorization' => 'Bearer ' . $this->getToken(),
            'XAUTH_TRAVELPORT_ACCESSGROUP' => $this->config['access_group'],
        ] + $defaultHeaders + $this->headers;
    }

    private function buildHttpClient(array $headers): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders($headers)
            ->timeout($this->requestTimeout)
            ->connectTimeout($this->connectTimeout)
            ->withOptions(['http_errors' => false]);
    }

    private function executeRequestWithRetries(\Illuminate\Http\Client\PendingRequest $http, string $url): Response
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt++ < $this->maxRetries) {
            try {
                $response = $this->executeRequest($http, $url);
                if ($response->successful() || !in_array($response->status(), [429, 503], true)) {
                    return $response;
                }
                $lastException = new RuntimeException("Request failed with status {$response->status()}");
            } catch (\Exception $e) {
                $lastException = $e;
            }

            if ($attempt < $this->maxRetries) {
                $delay = $this->retryDelayMs * (2 ** ($attempt - 1));
                Log::warning('Retrying Travelport API request', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'delay_ms' => $delay,
                    'error' => $lastException->getMessage(),
                ]);
                usleep($delay * 1000);
            }
        }

        throw $lastException ?? new RuntimeException('Max retries reached');
    }

    private function executeRequest(\Illuminate\Http\Client\PendingRequest $http, string $url): Response
    {

        $body = $this->getRequestBody();
        if (!empty($this->params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($this->params);
        }
        switch ($this->method) {
            case 'get':
                return $http->get($url, $this->params);
            case 'post':
                return $body === null
                    ? $http->post($url) // ✅ Send no body if not needed
                    : $http->post($url, empty($body)?null:$body);
            case 'put':
                return $http->put($url, $body);
            case 'delete':
                return $http->delete($url, empty($body) ? $this->params : $body);
            default:
                throw new RuntimeException("Unexpected method: {$this->method}");
        }
    }

    private function getRequestBody(): ?array
    {
        return $this->body;
    }

    private function checkCircuitBreaker(): void
    {
        $state = Cache::get(self::CIRCUIT_BREAKER_KEY . ':state', 'CLOSED');
        $failures = Cache::get(self::CIRCUIT_BREAKER_KEY . ':failures', 0);
        $lastAttempt = Cache::get(self::CIRCUIT_BREAKER_KEY . ':last_attempt', 0);

        if ($state === 'OPEN' && (time() - $lastAttempt) < $this->openTimeoutSeconds) {
            throw new CircuitBreakerOpenException('Circuit breaker is open');
        }

        if ($state === 'OPEN') {
            Cache::put(self::CIRCUIT_BREAKER_KEY . ':state', 'HALF_OPEN', $this->openTimeoutSeconds);
            Cache::put(self::CIRCUIT_BREAKER_KEY . ':half_open_attempts', 0, $this->openTimeoutSeconds);
        }

        if ($state === 'HALF_OPEN') {
            $attempts = Cache::increment(self::CIRCUIT_BREAKER_KEY . ':half_open_attempts');
            if ($attempts > $this->halfOpenAttempts) {
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':state', 'OPEN', $this->openTimeoutSeconds);
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':last_attempt', time(), $this->openTimeoutSeconds);
                throw new CircuitBreakerOpenException('Circuit breaker remains open after half-open attempts');
            }
        }
    }

    private function updateCircuitBreaker(bool $success): void
    {
        $state = Cache::get(self::CIRCUIT_BREAKER_KEY . ':state', 'CLOSED');
        $failures = Cache::get(self::CIRCUIT_BREAKER_KEY . ':failures', 0);

        if ($success) {
            if ($state !== 'CLOSED') {
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':state', 'CLOSED', $this->openTimeoutSeconds);
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':failures', 0, $this->openTimeoutSeconds);
                Log::info('Circuit breaker reset to CLOSED');
            }
        } else {
            $failures = Cache::increment(self::CIRCUIT_BREAKER_KEY . ':failures');
            if ($state === 'HALF_OPEN' || ($state === 'CLOSED' && $failures >= $this->failureThreshold)) {
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':state', 'OPEN', $this->openTimeoutSeconds);
                Cache::put(self::CIRCUIT_BREAKER_KEY . ':last_attempt', time(), $this->openTimeoutSeconds);
                Log::warning('Circuit breaker opened', ['failures' => $failures]);
            }
        }
    }

    private function logRequest(string $url, array $headers): void
    {
        Log::info('Travelport API Request', [
            'url' => $url,
            'method' => $this->method,
            'headers' => $this->sanitizeHeaders($headers),
            'params' => $this->params ?: null,
            'body' => $this->body ? (strlen(json_encode($this->body)) > 1000 ? '[TRUNCATED]' : $this->body) : null,
        ]);
    }

    private function logResponse(string $url, Response $response): void
    {
        $body = $response->body();
        $logData = [
            'url' => $url,
            'method' => $this->method,
            'status' => $response->status(),
            'body' => strlen($body) > 1000 ? '[TRUNCATED]' : $body,
        ];
        $response->successful() ? Log::info('Travelport API Response', $logData) : Log::error('Travelport API Response Failed', $logData);
    }

    private function logException(string $url, \Exception $e): void
    {
        Log::error('Travelport API Request Exception', [
            'url' => $url,
            'method' => $this->method,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = $headers;
        if (isset($sanitized['Authorization'])) {
            $sanitized['Authorization'] = 'Bearer [REDACTED]';
        }
        return $sanitized;
    }
}