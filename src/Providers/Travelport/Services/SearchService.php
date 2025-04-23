<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Providers\Travelport\Transformers\SearchTransformer;
use Exception;
use Illuminate\Support\Facades\Log;

class SearchService implements SearchServiceInterface
{
    protected $client;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    /**
     * Search for flights and return standardized response
     *
     * @param SearchRequestDTO $request
     * @return SearchResponseDTO
     */
    public function search(SearchRequestDTO $request): SearchResponseDTO
    {
        try {
            // Generate payload using the builder
            $payload = $this->buildFromSearchRequest($request);

            // Log the payload for debugging purposes
            Log::info('Travelport API request payload', $payload);

 

            // Send the request to Travelport API with updated base path and headers
            $response = $this->client
                ->request('POST', '/catalog/search/catalogproductofferings')  // Updated URL with base path
                ->withBody($payload)     // ✅ Corrected method name
                ->send();                // ✅ You should also replace `.get()` with `.send()` to match the client

            // Log the response for debugging purposes
            Log::info('Travelport API response', $response->json());

            // Check if the response is valid
            if ($response->status() === 404) {
                throw new Exception('404 Not Found: Check the API endpoint and credentials.');
            }

            if (!$response->successful()) {
                throw new Exception('Failed to fetch data from Travelport API.');
            }

            // Transform the response data using SearchTransformer
            $transformedData = SearchTransformer::transform($response->json());

            // Return the response as a DTO (use transformed data)
            return new SearchResponseDTO([$transformedData]);

        } catch (Exception $e) {
            // Handle any exceptions that occurred during the request
            Log::error('Error occurred while searching for flights', ['error' => $e->getMessage()]);
            throw new Exception('Error occurred while searching for flights: ' . $e->getMessage());
        }
    }



    /**
     * Build the payload from the SearchRequestDTO
     *
     * @param SearchRequestDTO $request
     * @return array
     */
    public function buildFromSearchRequest(SearchRequestDTO $request): array
    {
        return [
            'CatalogProductOfferingsQueryRequest' => [
                'CatalogProductOfferingsRequest' => [
                    '@type' => 'CatalogProductOfferingsRequestAir',
                    'offersPerPage' => 5,
                    'maxNumberOfUpsellsToReturn' => 0,
                    'contentSourceList' => ['NDC'],  // Content source (e.g., "GDS")
                    'PassengerCriteria' => [
                        [
                            '@type' => 'PassengerCriteria',
                            'number' => $request->getPassengers()['adults'],  // Assuming adults
                            'passengerTypeCode' => 'ADT',  // For adults, use "ADT"                        
                        ]
                    ],
                    'SearchCriteriaFlight' => [
                        [
                            '@type' => 'SearchCriteriaFlight',
                            'departureDate' => $request->getDepartureDate(),
                            'From' => [
                                'value' => $request->getOrigin(),
                            ],
                            'To' => [
                                'value' => $request->getDestination(),
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
