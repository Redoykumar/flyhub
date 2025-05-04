<?php

namespace Redoy\FlyHub\Providers\Amadeus\Transformers;

use Redoy\FlyHub\DTOs\Shared\FlightSegmentDTO;

class PriceTransformer
{
    private $responseData;
    private $request;

    public function __construct(array $responseData, $request)
    {
        $this->responseData = $responseData;
        $this->request = $request;
    }

    public function transform()
    {
        $flightOffer = $this->responseData['data']['flightOffers'][0] ?? [];
        $price = $flightOffer['price'] ?? [];
        $itineraries = $flightOffer['itineraries'][0]['segments'] ?? [];

        $flightSegments = [];
        foreach ($itineraries as $segment) {
            $flightSegments[] = FlightSegmentDTO::fromArray($segment);
        }

        return [
            'totalPrice' => (float) ($price['total'] ?? 0.0),
            'currency' => $price['currency'] ?? $this->request->currency ?? 'USD',
            'flightSegments' => $flightSegments,
            'meta' => [
                'provider' => 'Amadeus',
                'timestamp' => now()->toIso8601String(),
                'offerId' => $this->request->offerId,
            ],
        ];
    }
}