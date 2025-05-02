<?php

namespace Redoy\FlyHub\Providers\Amadeus\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Amadeus\AmadeusClient;
use Redoy\FlyHub\Providers\Amadeus\Transformers\SearchTransformer;

class SearchService implements SearchServiceInterface
{
    protected AmadeusClient $client;

    public function __construct(AmadeusClient $client)
    {
        $this->client = $client;
    }

    /**
     * Search for flights using Amadeus API and return standardized response.
     *
     * @param SearchRequestDTO $request
     * @return SearchResponseDTO
     */
    public function search(SearchRequestDTO $request): SearchResponseDTO
    {
        // Build the request payload from the SearchRequestDTO
        $payload = $this->buildFromSearchRequest($request);

        // Send the request to Amadeus API
        $response = $this->client
            ->request('POST', '/shopping/flight-offers')
            ->withBody($payload)
            ->send();
        // Transform the API response using SearchTransformer
        $transformedData = (new SearchTransformer($response->json(), $request))->transform();

        // Return standardized response
        return new SearchResponseDTO([
            [
                'provider' => $transformedData['provider'] ?? 'Amadeus',
                'flights' => $transformedData['flights'] ?? [],
                'meta' => $transformedData['meta'] ?? [],
            ]
        ]);
    }

    /**
     * Build the request body for the Amadeus flight search.
     *
     * @param SearchRequestDTO $request
     * @return array
     */
    protected function buildFromSearchRequest(SearchRequestDTO $request): array
    {
        $passengers = $request->getPassengers();
        $segments = $request->getSegments();
        $modifiers = $request->getModifiers();
        $pricing = $request->getPricing();

        // Map travelers
        $travelers = [];
        $travelerId = 1;

        if (!empty($passengers['adults'])) {
            for ($i = 0; $i < $passengers['adults']; $i++) {
                $travelers[] = [
                    'id' => (string) $travelerId++,
                    'travelerType' => 'ADULT',
                ];
            }
        }
        if (!empty($passengers['children'])) {
            for ($i = 0; $i < $passengers['children']; $i++) {
                $travelers[] = [
                    'id' => (string) $travelerId++,
                    'travelerType' => 'CHILD',
                ];
            }
        }
        if (!empty($passengers['infants'])) {
            for ($i = 0; $i < $passengers['infants']; $i++) {
                $travelers[] = [
                    'id' => (string) $travelerId++,
                    'travelerType' => 'HELD_INFANT',
                ];
            }
        }

        // Map origin-destinations
        $originDestinations = array_map(function ($segment, $index) {
            return [
                'id' => (string) ($index + 1),
                'originLocationCode' => $segment['from'],
                'destinationLocationCode' => $segment['to'],
                'departureDateTimeRange' => [
                    'date' => date('Y-m-d', strtotime($segment['date'])),
                ],
            ];
        }, $segments, array_keys($segments));

        // Build search criteria
        $searchCriteria = [
            'maxFlightOffers' => 10,
        ];

        if (!empty($modifiers['cabin'])) {
            $cabinMap = [
                'economy' => 'ECONOMY',
                'business' => 'BUSINESS',
                'first_class' => 'FIRST',
            ];
            $searchCriteria['flightFilters'] = [
                'cabinRestrictions' => [
                    [
                        'cabin' => $cabinMap[$modifiers['cabin']] ?? 'ECONOMY',
                        'coverage' => 'ALL_SEGMENTS',
                        'originDestinationIds' => array_map(fn($index) => (string) ($index + 1), array_keys($segments)),
                    ],
                ],
            ];
        }

        if (!empty($segments[0]['airlines'])) {
            $searchCriteria['flightFilters']['carrierRestrictions'] = [
                'includedCarrierCodes' => array_map('strtoupper', $segments[0]['airlines']),
            ];
        }

        // Construct the payload
        $payload = [
            'currencyCode' => $pricing['currency'] ?? 'USD',
            'originDestinations' => $originDestinations,
            'travelers' => $travelers,
            'sources' => ['GDS'],
            'searchCriteria' => $searchCriteria,
        ];

        return $payload;
    }
}