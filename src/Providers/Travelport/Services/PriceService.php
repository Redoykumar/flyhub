<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Contracts\Services\PriceServiceInterface;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Providers\Travelport\Transformers\PriceTransformer;

class PriceService implements PriceServiceInterface
{
    protected $client;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    /**
     * Build the request body for Travelport AirPrice API.
     *
     * @param PriceRequestDTO $request
     * @return array
     */
    protected function buildBody(PriceRequestDTO $request): array
    {
        $catalogOfferingsId = $request->offerId; // Transaction ID from Search response
        $flightIds = $request->flightIds; // Offer IDs from Search response

        $body = [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequestAir' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => [
                            'value' => $catalogOfferingsId,
                        ],
                    ],
                    'CatalogProductOfferingSelection' => [],
                    'PassengerCriteria' => [],
                ],
            ],
        ];

        // Add CatalogProductOfferingSelection for each flight ID
        foreach ($flightIds as $index => $flightId) {
            $body['OfferQueryBuildFromCatalogProductOfferings']['BuildFromCatalogProductOfferingsRequestAir']['CatalogProductOfferingSelection'][] = [
                'CatalogProductOfferingIdentifier' => [
                    'Identifier' => [
                        'value' => $flightId,
                    ],
                ],
                'ProductIdentifier' => [
                    [
                        'Identifier' => [
                            'value' => "p{$index}", // Product ID, e.g., p0, p1
                        ],
                    ],
                ],
            ];
        }

        // Add PassengerCriteria based on passengerCount
        $passengerCount = $request->passengerCount ?? 1;
        $body['OfferQueryBuildFromCatalogProductOfferings']['BuildFromCatalogProductOfferingsRequestAir']['PassengerCriteria'] = [
            [
                '@type' => 'PassengerCriteria',
                'number' => $passengerCount,
                'passengerTypeCode' => 'ADT', // Default to adult; extend for other PTCs if needed
            ],
        ];

        // Optional: Add pricing modifiers (e.g., lowFareFinderInd, validateInventoryInd)
        $body['OfferQueryBuildFromCatalogProductOfferings']['lowFareFinderInd'] = false;
        $body['OfferQueryBuildFromCatalogProductOfferings']['validateInventoryInd'] = true;

        return $body;
    }

    public function getPrice(PriceRequestDTO $request): PriceResponseDTO
    {


        // Build the request payload
        $payload = $this->buildBody($request);
        dd($payload);
        // Send the request to Travelport API
        $this->client->request('POST', '/price/offers/buildfromcatalogproductofferings')
            ->withBody($payload);

        $response = $this->client->send();

        if (!$response->successful()) {
            throw new \Exception('Travelport pricing failed: ' . $response->body());
        }

        // Transform the API response
        $transformedData = (new PriceTransformer($response->json(), $request))->transform();

        return new PriceResponseDTO(
            $transformedData['totalPrice'],
            $transformedData['currency'],
            $transformedData['flightSegments'],
            $transformedData['meta']
        );
    }
}