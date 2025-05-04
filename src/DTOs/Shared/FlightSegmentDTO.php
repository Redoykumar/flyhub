<?php

namespace Redoy\FlyHub\DTOs\Shared;

class FlightSegmentDTO
{
    public string $airline;
    public string $flightNumber;
    public string $departureTime;
    public string $arrivalTime;
    public string $origin;
    public string $destination;

    public function __construct(
        string $airline,
        string $flightNumber,
        string $departureTime,
        string $arrivalTime,
        string $origin,
        string $destination
    ) {
        $this->airline = $airline;
        $this->flightNumber = $flightNumber;
        $this->departureTime = $departureTime;
        $this->arrivalTime = $arrivalTime;
        $this->origin = $origin;
        $this->destination = $destination;
    }

    /**
     * Create from array (e.g., from API response data).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['airline'] ?? $data['carrierCode'] ?? 'Unknown',
            $data['flightNumber'] ?? $data['number'] ?? 'Unknown',
            $data['departureTime'] ?? $data['departure']['at'] ?? '1970-01-01T00:00:00',
            $data['arrivalTime'] ?? $data['arrival']['at'] ?? '1970-01-01T00:00:00',
            $data['origin'] ?? $data['departure']['iataCode'] ?? 'Unknown',
            $data['destination'] ?? $data['arrival']['iataCode'] ?? 'Unknown'
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'airline' => $this->airline,
            'flightNumber' => $this->flightNumber,
            'departureTime' => $this->departureTime,
            'arrivalTime' => $this->arrivalTime,
            'origin' => $this->origin,
            'destination' => $this->destination,
        ];
    }
}