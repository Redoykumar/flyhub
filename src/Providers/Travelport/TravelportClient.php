<?php

namespace Redoy\FlyHub\Providers\Travelport;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class TravelportClient
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

        $env = $this->config['environment'] ?? 'preproduction';
        $this->baseUrl = $this->config['base_urls'][$env] ?? 'https://api.pp.travelport.com/11/air';
        $this->authUrl = $this->config['auth_urls'][$env] ?? 'https://oauth.pp.travelport.com/oauth/oauth20/token';
    }

    /**
     * Retrieve the access token for the Travelport API.
     * This token is cached for 23 hours.
     *
     * @return string
     * @throws \Exception
     */
    protected function getToken(): string
    {
        return Cache::remember('travelport_token', 82800, function () {
            $response = Http::asForm()->post($this->authUrl, [
                'grant_type' => 'password',
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'scope' => 'openid',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Travelport OAuth failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Set custom headers for the request.
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
     * Set query parameters for GET requests.
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
     * Set the HTTP request method and endpoint.
     *
     * @param string $method
     * @param string $endpoint
     * @return self
     */
    public function request(string $method, string $endpoint): self
    {
        $this->method = strtolower($method);
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * Set the body (payload) for POST requests.
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
     * Perform the API request.
     *
     * @return Response
     * @throws \Exception
     */
    public function send(): Response
    {
        $token = $this->getToken();

        // Prepare headers
        $headers = array_merge($this->headers, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Accept-Version' => '11',
            'Content-Version' => '11',
            'XAUTH_TRAVELPORT_ACCESSGROUP' => $this->config['access_group'] ?? '',
            'taxBreakDown' => 'false',
        ]);

        // Log the request for debugging
        \Log::info('Travelport API Request', [
            'url' => "{$this->baseUrl}{$this->endpoint}",
            'method' => $this->method,
            'headers' => $headers,
            'params' => $this->params,
            'body' => $this->body,
        ]);

        // Make the HTTP request with parameters (for GET) or body (for POST)
        if ($this->method === 'get') {
            return Http::withToken($token)->withHeaders($headers)
                ->get("{$this->baseUrl}{$this->endpoint}", $this->params);
        } else { // POST, PUT, DELETE, etc.
            return Http::withToken($token)->withHeaders($headers)
                ->withBody(json_encode($this->body), 'application/json')
                ->post("{$this->baseUrl}{$this->endpoint}", $this->body);
        }
    }
}
