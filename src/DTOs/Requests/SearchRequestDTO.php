<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Illuminate\Support\Facades\Validator;

class SearchRequestDTO
{
    public $trip_type;
    public $passengers;
    public $origin;
    public $destination;
    public $departure_date;
    public $return_date;
    public $preferred_airline;
    public $stops;
    public $fare_type;
    public $price_range;

    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'trip_type' => 'required|in:one-way,round-trip',
            'passengers' => 'required|array',
            'passengers.adults' => 'required|integer|min:1',
            'passengers.children' => 'integer|min:0',
            'passengers.infants' => 'integer|min:0',
            'origin' => 'required|string|size:3',
            'destination' => 'required|string|size:3',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'nullable|date|after:departure_date|required_if:trip_type,round-trip',
            'preferred_airline' => 'nullable|string',
            'stops' => 'nullable|in:non-stop,one-stop,multi-stop',
            'fare_type' => 'nullable|in:economy,business,first_class',
            'price_range' => 'nullable|array',
            'price_range.min' => 'nullable|numeric|min:0',
            'price_range.max' => 'nullable|numeric|gte:price_range.min',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        $this->trip_type = $data['trip_type'] ?? null;
        $this->passengers = $data['passengers'] ?? [];
        $this->origin = $data['origin'] ?? null;
        $this->destination = $data['destination'] ?? null;
        $this->departure_date = $data['departure_date'] ?? null;
        $this->return_date = $data['return_date'] ?? null;
        $this->preferred_airline = $data['preferred_airline'] ?? null;
        $this->stops = $data['stops'] ?? null;
        $this->fare_type = $data['fare_type'] ?? null;
        $this->price_range = $data['price_range'] ?? ['min' => 0, 'max' => PHP_INT_MAX];
    }

    // Getter methods
    public function getTripType()
    {
        return $this->trip_type;
    }

    public function getPassengers()
    {
        return $this->passengers;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function getDepartureDate()
    {
        return $this->departure_date;
    }

    public function getReturnDate()
    {
        return $this->return_date;
    }

    public function getPreferredAirline()
    {
        return $this->preferred_airline;
    }

    public function getStops()
    {
        return $this->stops;
    }

    public function getFareType()
    {
        return $this->fare_type;
    }

    public function getPriceRange()
    {
        return $this->price_range;
    }
}
