<?php

namespace Redoy\FlyHub\Providers\Amadeus;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AmadeusClient
{
    protected string $baseUrl;
    protected string $authUrl;
    protected array $config;

    protected string $method = 'post';
    protected string $endpoint = '';
    protected array $headers = [];
    protected array $params = [];  // For GET request parameters
    protected array $body = [];    // For POST request body

    public function __construct(array $config)
    {
        $this->config = $config;
        $env = $this->config['environment'] ?? 'sandbox';
        $this->baseUrl = rtrim($this->config['base_urls'][$env] ?? '', '/');
        $this->authUrl = $this->config['auth_urls'][$env] ?? '';
    }

    /**
     * Retrieve the access token for Amadeus.
     *
     * @return string
     * @throws \Exception
     */
    protected function getToken(): string
    {
        return Cache::remember('amadeus_token', 1700, function () {
            $response = Http::asForm()->post($this->authUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['api_key'],
                'client_secret' => $this->config['api_secret'],
            ]);

            if (!$response->successful()) {
                throw new \Exception('Amadeus token request failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Set request headers.
     *
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set GET query parameters.
     *
     * @param array $params
     * @return self
     */
    public function withParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set request method and endpoint.
     *
     * @param string $method
     * @param string $endpoint
     * @return self
     */
    public function request(string $method, string $endpoint): self
    {
        $this->method = strtolower($method);
        $this->endpoint = ltrim($endpoint, '/');
        return $this;
    }

    /**
     * Set POST body payload.
     *
     * @param array $body
     * @return self
     */
    public function withBody(array $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Send the HTTP request.
     *
     * @return Response
     * @throws \Exception
     */
    public function send(): Response
    {
        $token = $this->getToken();

        $headers = array_merge($this->headers, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $url = "{$this->baseUrl}/{$this->endpoint}";

        Log::info('Amadeus API Request', [
            'url' => $url,
            'method' => $this->method,
            'headers' => $headers,
            'params' => $this->params,
            'body' => $this->body,
        ]);

        if ($this->method === 'get') {
            return Http::withToken($token)
                ->withHeaders($headers)
                ->get($url, $this->params);
        }

        return Http::withToken($token)
            ->withHeaders($headers)
            ->withBody(json_encode($this->body), 'application/json')
            ->send(strtoupper($this->method), $url);
    }
}
