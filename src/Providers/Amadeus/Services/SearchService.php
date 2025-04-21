<?php

namespace Redoy\FlyHub\Providers\Amadeus\Services;

use Redoy\FlyHub\Providers\Amadeus\AmadeusClient;

class SearchService
{
    // Reference to Amadeus client
    protected $client;

    // Constructor injects AmadeusClient
    public function __construct(AmadeusClient $client)
    {
        $this->client = $client;
    }

    // Mock search implementation (replace with real API call later)
    public function search($request)
    {
        return [
            'provider' => 'Amadeus',
            'flights' => [
                [
                    'departure' => '2025-05-01 09:00',
                    'arrival' => '2025-05-01 13:00',
                    'airline' => 'Lufthansa',
                    'price' => 350,
                    'stops' => 1,
                ],
                [
                    'departure' => '2025-05-01 15:00',
                    'arrival' => '2025-05-01 19:00',
                    'airline' => 'Air France',
                    'price' => 400,
                    'stops' => 0,
                ],
            ],
        ];
    }
}