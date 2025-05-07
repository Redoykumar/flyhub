<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Providers\Travelport\Transformers\SearchTransformer;

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

        // Send the request to Travelport API
        $response = $this->client
            ->request('POST', '/catalog/search/catalogproductofferings')
            ->withBody($payload)
            ->send();
        // Transform the response data using SearchTransformer
        $transformedData = (new SearchTransformer($response->json(), $request))->transform();

        return new SearchResponseDTO([
            [
                // 'provider' => $transformedData['provider'],
                'data' => $transformedData['flights'],
                // 'meta' => $transformedData['meta'],
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
        $passengers = $request->getPassengers();
        $segments = $request->getSegments();
        $modifiers = $request->getModifiers();

        // Map passenger types
        $passengerCriteria = [];
        if (!empty($passengers['adults'])) {
            $passengerCriteria[] = [
                '@type' => 'PassengerCriteria',
                'number' => $passengers['adults'],
                'passengerTypeCode' => 'ADT',
            ];
        }
        if (!empty($passengers['children'])) {
            $passengerCriteria[] = [
                '@type' => 'PassengerCriteria',
                'number' => $passengers['children'],
                'passengerTypeCode' => 'CNN',
                'age' => 8, // Default age for children
            ];
        }
        if (!empty($passengers['infants'])) {
            $passengerCriteria[] = [
                '@type' => 'PassengerCriteria',
                'number' => $passengers['infants'],
                'passengerTypeCode' => 'INF',
                'age' => 1, // Default age for infants
            ];
        }

        // Map flight segments
        $searchCriteriaFlight = array_map(function ($segment) {
            $criteria = [
                '@type' => 'SearchCriteriaFlight',
                'departureDate' => date('Y-m-d', strtotime($segment['date'])),
                'From' => ['value' => $segment['from']],
                'To' => ['value' => $segment['to']],
            ];

            if (!empty($segment['timeRange']['start']) && !empty($segment['timeRange']['end'])) {
                $criteria['DepartureTimeRange'] = [
                    'start' => $segment['timeRange']['start'],
                    'end' => $segment['timeRange']['end'],
                ];
            }

            return $criteria;
        }, $segments);

        // Build search modifiers
        $searchModifiersAir = ['@type' => 'SearchModifiersAir'];
        if (!empty($modifiers['cabin'])) {
            $cabinMap = [
                'economy' => 'Economy',
                'business' => 'Business',
                'first_class' => 'First',
            ];
            $searchModifiersAir['CabinPreference'] = [
                [
                    '@type' => 'CabinPreference',
                    'preferenceType' => 'Permitted',
                    'cabins' => [$cabinMap[$modifiers['cabin']] ?? 'Economy'],
                ],
            ];
        }
        if (!empty($segments[0]['airlines'])) {
            $searchModifiersAir['CarrierPreference'] = [
                [
                    '@type' => 'CarrierPreference',
                    'preferenceType' => 'Preferred',
                    'carriers' => $segments[0]['airlines'],
                ],
            ];
        }

        // Construct the payload
        $payload = [
            'CatalogProductOfferingsQueryRequest' => [
                'CatalogProductOfferingsRequest' => [
                    '@type' => 'CatalogProductOfferingsRequestAir',
                    'offersPerPage' => 15,
                    'maxNumberOfUpsellsToReturn' => 0,
                    'contentSourceList' => ['NDC'],
                    'PassengerCriteria' => $passengerCriteria,
                    'SearchCriteriaFlight' => $searchCriteriaFlight,
                ],
            ],
        ];

        // Add search modifiers if applicable
        if (!empty($searchModifiersAir['CabinPreference']) || !empty($searchModifiersAir['CarrierPreference'])) {
            $payload['CatalogProductOfferingsQueryRequest']['CatalogProductOfferingsRequest']['SearchModifiersAir'] = $searchModifiersAir;
        }

        return $payload;
    }
}