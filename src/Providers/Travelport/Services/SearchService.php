<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;

class SearchService implements SearchServiceInterface
{
    protected $client;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    // Search for flights and return standardized response
    public function search(SearchRequestDTO $request): SearchResponseDTO
    {
        // Mock data (replace with real API call later)
        $data = [
            'provider' => 'Travelport',
            'flights' => [
                [
                    'departure' => '2025-05-01 10:00',
                    'arrival' => '2025-05-01 14:00',
                    'airline' => 'Delta',
                    'price' => 300,
                    'stops' => 0,
                    'fare_type' => 'non-refundable'
                ],
            ],
        ];

        return new SearchResponseDTO([$data]);
    }
}