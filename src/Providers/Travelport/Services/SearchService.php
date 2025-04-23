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

        // Generate payload using the builder
        $payload = $this->buildFromSearchRequest($request);


        // Send the request to Travelport API with updated base path and headers
        $response = $this->client
            ->request('POST', '/catalog/search/catalogproductofferings')  // Updated URL with base path
            ->withBody($payload)     // ✅ Corrected method name
            ->send();
        // Transform the response data using SearchTransformer
        $transformedData = SearchTransformer::transform($response->json());
        dd($transformedData);
        // Return the response as a DTO (use transformed data)
        return new SearchResponseDTO([
            [
                'provider' => 'Travelport',
                'flights' => $transformedData['flights'],
            ]
        ]);


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
                    'contentSourceList' => config('flyhub.providers.travelport.content_source_list'),  // Content source (e.g., "GDS")
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
