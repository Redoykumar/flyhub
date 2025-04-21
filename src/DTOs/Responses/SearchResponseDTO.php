<?php

namespace Redoy\FlyHub\DTOs\Responses;

use Redoy\FlyHub\DTOs\Shared\FlightSegmentDTO;

class SearchResponseDTO
{
    public $flights;

    public function __construct(array $flights)
    {
        $this->flights = $flights; // Array of FlightSegmentDTO objects
    }
}