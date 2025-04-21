<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Providers\Travelport\TravelportClient;

class SearchService
{
    // Reference to Travelport client
    protected $client;

    // Constructor injects TravelportClient
    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    // Mock search implementation (replace with real API call later)
    public function search($request)
    {
        return [
            'provider' => 'Travelport',
            'flights' => [
                [
                    'departure' => '2025-05-01 10:00',
                    'arrival' => '2025-05-01 14:00',
                    'airline' => 'Delta',
                    'price' => 300,
                    'stops' => 0,
                ],
            ],
        ];
    }
}