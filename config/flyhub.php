<?php

return [
    'default_provider' => 'travelport',

    'providers' => [
        'travelport' => [
            'client' => \Redoy\FlyHub\Providers\Travelport\TravelportClient::class,
            'search' => \Redoy\FlyHub\Providers\Travelport\Services\SearchService::class,
            'price' => \Redoy\FlyHub\Providers\Travelport\Services\PriceService::class,
            'book' => \Redoy\FlyHub\Providers\Travelport\Services\BookingService::class,
            'payment' => \Redoy\FlyHub\Providers\Travelport\Services\PaymentService::class,
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
            'content_source_list' => explode(',', env('TRAVELPORT_CONTENT_SOURCES', 'NDC')),
        ],

        'amadeus' => [
            'client' => \Redoy\FlyHub\Providers\Amadeus\AmadeusClient::class,
            'search' => \Redoy\FlyHub\Providers\Amadeus\Services\SearchService::class,
            'price' => \Redoy\FlyHub\Providers\Amadeus\Services\PriceService::class,
            'environment' => env('AMADEUS_ENV', 'sandbox'), // sandbox | production
            'base_urls' => [
                'sandbox' => 'https://test.api.amadeus.com/v2',
                'production' => 'https://api.amadeus.com/v2',
            ],
            'auth_urls' => [
                'sandbox' => 'https://test.api.amadeus.com/v1/security/oauth2/token',
                'production' => 'https://api.amadeus.com/v1/security/oauth2/token',
            ],
            'api_key' => env('AMADEUS_API_KEY'),
            'api_secret' => env('AMADEUS_API_SECRET'),
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
