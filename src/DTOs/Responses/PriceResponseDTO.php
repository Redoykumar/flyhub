<?php

namespace Redoy\FlyHub\DTOs\Responses;

use Redoy\FlyHub\DTOs\Shared\FlightSegmentDTO;

class PriceResponseDTO
{
    public float $totalPrice;
    public string $currency;
    /** @var FlightSegmentDTO[] */
    public array $flightSegments;
    public array $meta;

    public function __construct(float $totalPrice, string $currency, array $flightSegments = [], array $meta = [])
    {
        $this->totalPrice = $totalPrice;
        $this->currency = $currency;
        $this->flightSegments = $flightSegments;
        $this->meta = $meta;
    }
}