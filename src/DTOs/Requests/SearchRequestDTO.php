<?php

namespace Redoy\FlyHub\DTOs\Requests;

class SearchRequestDTO
{
    public $origin;
    public $destination;
    public $date;
    public $tripType;
    public $passengers;
    public $filters;

    public function __construct(
        string $origin,
        string $destination,
        string $date,
        string $tripType,
        array $passengers,
        array $filters = []
    ) {
        $this->origin = $origin;
        $this->destination = $destination;
        $this->date = $date;
        $this->tripType = $tripType;
        $this->passengers = $passengers;
        $this->filters = $filters;
    }
}