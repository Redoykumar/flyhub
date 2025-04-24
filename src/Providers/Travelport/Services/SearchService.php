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
        // $data = [
        //     'provider' => 'Travelport',
        //     'flights' => [
        //         [
        //             'id' => 'FLT_TRP_20250424_001',
        //             'provider' => 'travelport',
        //             'fare_type' => 'ECONOMY',
        //             'total_duration' => 'PT8H30M',
        //             'stops' => 1,
        //             'price' => [
        //                 'amount' => 480.5,
        //                 'currency' => 'USD',
        //                 'breakdown' => [
        //                     'base' => 420.0,
        //                     'tax' => 60.5,
        //                 ],
        //                 'currency_conversion' => [
        //                     'from' => 'USD',
        //                     'to' => 'EUR',
        //                     'rate' => 0.92,
        //                 ],
        //             ],
        //             'segments' => [
        //                 [
        //                     'segment_number' => 1,
        //                     'from' => [
        //                         'airport' => 'DAC',
        //                         'city' => 'Dhaka',
        //                         'country' => 'BD',
        //                         'time' => '2025-06-01T09:00:00Z',
        //                     ],
        //                     'to' => [
        //                         'airport' => 'DXB',
        //                         'city' => 'Dubai',
        //                         'country' => 'AE',
        //                         'time' => '2025-06-01T13:30:00Z',
        //                     ],
        //                     'flight_number' => 'EK583',
        //                     'airline' => [
        //                         'code' => 'EK',
        //                         'name' => 'Emirates',
        //                         'icon' => 'https://cdn.example.com/airlines/EK.png',
        //                     ],
        //                     'aircraft' => 'Boeing 777',
        //                     'duration' => 'PT4H30M',
        //                     'flight_class' => 'Economy',
        //                     'cabin_type' => 'Standard',
        //                     'layover' => [
        //                         'duration' => 'PT1H30M',
        //                         'location' => 'Doha',
        //                     ],
        //                 ],
        //             ],
        //             'conditions' => [
        //                 'is_refundable' => true,
        //                 'baggage' => [
        //                     'checked' => '30kg',
        //                     'carry_on' => '7kg',
        //                 ],
        //                 'terms_and_conditions' => [
        //                     'cancellation_policy' => 'https://example.com/policies/cancel',
        //                     'change_policy' => 'https://example.com/policies/change',
        //                 ],
        //             ],
        //             'in_flight_amenities' => [
        //                 'wifi' => true,
        //                 'meal' => 'Vegetarian',
        //                 'entertainment' => true,
        //             ],
        //             'availability' => [
        //                 'seats_remaining' => 12,
        //                 'quota' => 'Limited',
        //             ],
        //             'airline_contact' => [
        //                 'phone' => '+123456789',
        //                 'email' => 'support@emirates.com',
        //             ],
        //             'booking_token' => 'ABC123_SECURETOKEN',
        //         ],
        //     ],
        //     'meta' => [
        //         'search_id' => 'SRCH_20250424_0001',
        //         'origin' => 'DAC',
        //         'destination' => 'DXB',
        //         'departure_date' => '2025-06-01',
        //         'round_trip' => false,
        //         'currency' => 'USD',
        //         'total_results' => 12,
        //     ],
        // ];


        // return new SearchResponseDTO([$data]);
        // Generate payload using the builder
        $payload = $this->buildFromSearchRequest($request);


        // Send the request to Travelport API with updated base path and headers
        $response = $this->client
            ->request('POST', '/catalog/search/catalogproductofferings')  // Updated URL with base path
            ->withBody($payload)     // ✅ Corrected method name
            ->send();
        // Transform the response data using SearchTransformer
        $transformedData = SearchTransformer::transform($response->json());
        return new SearchResponseDTO([
            [
                'provider' => $transformedData['provider'],
                'flights' => $transformedData['flights'],
                'meta' => $transformedData['meta'],
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
