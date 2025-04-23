<?php

namespace Redoy\FlyHub\Providers\Amadeus\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Amadeus\AmadeusClient;

class SearchService implements SearchServiceInterface
{
    protected $client;

    public function __construct(AmadeusClient $client)
    {
        $this->client = $client;
    }

    // Search for flights and return standardized response
    public function search(SearchRequestDTO $request): SearchResponseDTO
    {
        // Mock data (replace with real API call later)
        $data = [
            'provider' => 'Amadeus',
            'flights' => [
                [
                    'departure' => '2025-05-01 09:00',
                    'arrival' => '2025-05-01 13:00',
                    'airline' => 'Lufthansa',
                    'price' => 350,
                    'stops' => 1,
                    'fare_type' => 'refundable'
                ],
                [
                    'departure' => '2025-05-01 15:00',
                    'arrival' => '2025-05-01 19:00',
                    'airline' => 'Air France',
                    'price' => 400,
                    'stops' => 0,
                    'fare_type' => 'non-refundable'
                ],
            ],
        ];

        return new SearchResponseDTO([$data]);
    }
}