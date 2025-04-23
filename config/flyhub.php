<?php

return [
    'default_provider' => 'travelport',

    'providers' => [
        'travelport' => [
            'class' => \Redoy\FlyHub\Providers\Travelport\TravelportClient::class,
            'api_key' => env('TRAVELPORT_API_KEY'),
            'endpoint' => env('TRAVELPORT_ENDPOINT'),
        ],
        'amadeus' => [
            'class' => \Redoy\FlyHub\Providers\Amadeus\AmadeusClient::class,
            'api_key' => env('AMADEUS_API_KEY'),
            'endpoint' => env('AMADEUS_ENDPOINT'),
        ],
    ],
    'pricing' => [
        'source' => env('FLIGHT_PRICING_SOURCE', 'config'), // 'config' or 'database'
        'rules' => [
            'economy' => [
                'markup_percentage' => 5,
                'fixed_fee' => 10,
                'discount_percentage' => 0,
            ],
            'business' => [
                'markup_percentage' => 8,
                'fixed_fee' => 20,
                'discount_percentage' => 0,
            ],
            'first_class' => [
                'markup_percentage' => 10,
                'fixed_fee' => 50,
                'discount_percentage' => 0,
            ],
        ],
        'providers' => [
            'travelport' => [
                'markup_percentage' => env('FLIGHT_TRAVELPORT_MARKUP', 10),
                'fixed_fee' => env('FLIGHT_TRAVELPORT_FIXED_FEE', 15),
                'discount_percentage' => env('FLIGHT_TRAVELPORT_DISCOUNT', 0),
            ],
            'amadeus' => [
                'markup_percentage' => env('FLIGHT_AMADEUS_MARKUP', 8),
                'fixed_fee' => env('FLIGHT_AMADEUS_FIXED_FEE', 12),
                'discount_percentage' => env('FLIGHT_AMADEUS_DISCOUNT', 0),
            ],
        ],
        'currency' => env('FLIGHT_PRICING_CURRENCY', 'USD'),
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Cache TTL in seconds
    ],
];