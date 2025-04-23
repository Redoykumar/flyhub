<?php

return [
    'default_provider' => 'travelport',

    'providers' => [
        'travelport' => [
            'class' => \Redoy\FlyHub\Providers\Travelport\TravelportClient::class,
            'environment' => env('TRAVELPORT_ENV', 'preproduction'), // preproduction | production
            'base_urls' => [
                'preproduction' => 'https://api.pp.travelport.com/11/air',
                'production' => 'https://api.travelport.com/11/air',
            ],
            'auth_urls' => [
                'preproduction' => 'https://oauth.pp.travelport.com/oauth/oauth20/token',
                'production' => 'https://oauth.travelport.com/oauth/oauth20/token',
            ],
            'username' => env('TRAVELPORT_USERNAME'),
            'password' => env('TRAVELPORT_PASSWORD'),
            'client_id' => env('TRAVELPORT_CLIENT_ID'),
            'client_secret' => env('TRAVELPORT_CLIENT_SECRET'),
            'access_group' => env('TRAVELPORT_ACCESSGROUP'),
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
