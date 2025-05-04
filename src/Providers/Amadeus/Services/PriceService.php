<?php

namespace Redoy\FlyHub\Providers\Amadeus\Services;

use Redoy\FlyHub\Contracts\Services\PriceServiceInterface;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;
use Redoy\FlyHub\Providers\Amadeus\AmadeusClient;
use Redoy\FlyHub\Providers\Amadeus\Transformers\PriceTransformer;

class PriceService implements PriceServiceInterface
{
    protected $client;

    public function __construct(AmadeusClient $client)
    {
        $this->client = $client;
    }

    public function getPrice(PriceRequestDTO $request): PriceResponseDTO
    {
        if (empty($request->flightIds)) {
            throw new \Exception("At least one flight ID is required for pricing.");
        }

        $this->client->request('POST', '/shopping/flight-offers/pricing')
            ->withBody([
                'data' => [
                    'type' => 'flight-offers-pricing',
                    'flightOffers' => array_map(function ($flightId) {
                        return [
                            'type' => 'flight-offer',
                            'id' => $flightId,
                        ];
                    }, $request->flightIds),
                ],
            ]);

        $response = $this->client->send();

        if (!$response->successful()) {
            throw new \Exception('Amadeus pricing failed: ' . $response->body());
        }

        $transformedData = (new PriceTransformer($response->json(), $request))->transform();

        return new PriceResponseDTO(
            $transformedData['totalPrice'],
            $transformedData['currency'],
            $transformedData['flightSegments'],
            $transformedData['meta']
        );
    }
}