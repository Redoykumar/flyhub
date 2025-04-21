<?php

return [
    'default_provider' => 'travelport',

    'providers' => [
        'travelport' => [
            'class' => \Redoy\FlyHub\Providers\Travelport\TravelportClient::class,
            'api_key' => env('TRAVELPORT_API_KEY'),
            'endpoint' => env('TRAVELPORT_ENDPOINT'),
            'markup' => 5, // 5% markup
        ],
        'amadeus' => [
            'class' => \Redoy\FlyHub\Providers\Amadeus\AmadeusClient::class,
            'api_key' => env('AMADEUS_API_KEY'),
            'endpoint' => env('AMADEUS_ENDPOINT'),
            'markup' => 3, // 3% markup
        ],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Cache TTL in seconds
    ],
];