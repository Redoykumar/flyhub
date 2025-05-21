<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object for flight search requests.
 */
class SearchRequestDTO
{
    private string $tripType;
    private array $passengers;
    private array $segments;
    private array $modifiers;
    private array $pricing;
    private array $extras;
    private string $searchId;

    /**
     * SearchRequestDTO constructor.
     *
     * @param array $data Input data for flight search
     * @throws InvalidArgumentException If validation fails
     */
    public function __construct(array $data)
    {

        $this->validate($data);
        $this->setProperties($data);
    }

    /**
     * Validates the input data against defined rules.
     *
     * @param array $data Input data to validate
     * @throws InvalidArgumentException If validation fails
     */
    private function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'trip_type' => ['required', 'in:one-way,round-trip,multi-city'],
            'passengers' => ['required', 'array'],
            'passengers.adults' => ['required', 'integer', 'min:1'],
            'passengers.children' => ['integer', 'min:0'],
            'passengers.infants' => ['integer', 'min:0'],
            'segments' => ['required', 'array', 'min:1'],
            'segments.*.date' => ['required', 'date', 'after_or_equal:today'],
            'segments.*.from' => ['required', 'string', 'size:3'],
            'segments.*.to' => ['required', 'string', 'size:3'],
            'segments.*.timeRange.start' => ['nullable', 'date_format:H:i:s'],
            'segments.*.timeRange.end' => ['nullable', 'date_format:H:i:s'],
            'segments.*.airlines' => ['nullable', 'array'],
            'segments.*.stops' => ['nullable', 'in:non-stop,one-stop,multi-stop'],
            'segments.*.maxPrice' => ['nullable', 'numeric', 'min:0'],
            'segments.*.priceRange.min' => ['nullable', 'numeric', 'min:0'],
            'segments.*.priceRange.max' => ['nullable', 'numeric', 'gte:segments.*.priceRange.min'],
            'modifiers' => ['nullable', 'array'],
            'modifiers.cabin' => ['nullable', 'in:economy,business,first_class'],
            'modifiers.flexDates' => ['nullable', 'boolean'],
            'modifiers.oneWay' => ['nullable', 'boolean'],
            'modifiers.directOnly' => ['nullable', 'boolean'],
            'modifiers.class' => ['nullable', 'string'],
            'modifiers.multiDest' => ['nullable', 'boolean'],
            'modifiers.nearbyAirports' => ['nullable', 'boolean'],
            'modifiers.layoverRange.min' => ['nullable', 'numeric', 'min:0'],
            'modifiers.layoverRange.max' => ['nullable', 'numeric', 'gte:modifiers.layoverRange.min'],
            'pricing' => ['nullable', 'array'],
            'pricing.currency' => ['nullable', 'string'],
            'pricing.fareType' => ['nullable', 'in:economy,business,first_class'],
            'pricing.maxPrice' => ['nullable', 'numeric', 'min:0'],
            'pricing.minPrice' => ['nullable', 'numeric', 'gte:pricing.maxPrice'],
            'pricing.pricingType' => ['nullable', 'in:perPerson'],
            'pricing.advancePurchase' => ['nullable', 'integer', 'min:0'],
            'pricing.discounted' => ['nullable', 'boolean'],
            'extras' => ['nullable', 'array'],
            'extras.meals' => ['nullable', 'array'],
            'extras.baggage' => ['nullable', 'string'],
            'extras.seat' => ['nullable', 'string'],
            'extras.wheelchair' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    /**
     * Sets the DTO properties from validated data.
     *
     * @param array $data Validated input data
     */
    private function setProperties(array $data): void
    {
        $this->tripType = $data['trip_type'];
        $this->passengers = $data['passengers'] ?? [];
        $this->segments = $data['segments'] ?? [];
        $this->modifiers = $data['modifiers'] ?? [];
        $this->pricing = $data['pricing'] ?? [];
        $this->extras = $data['extras'] ?? [];
        $this->searchId = $this->generateSearchId();

    }

    /**
     * Gets the trip type.
     *
     * @return string
     */
    public function getTripType(): string
    {
        return $this->tripType;
    }

    /**
     * Gets the passengers information.
     *
     * @return array
     */
    public function getPassengers(): array
    {
        return $this->passengers;
    }

    /**
     * Gets the flight segments.
     *
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Gets the search modifiers.
     *
     * @return array
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
    }

    /**
     * Gets the pricing information.
     *
     * @return array
     */
    public function getPricing(): array
    {
        return $this->pricing;
    }

    /**
     * Gets the extra services information.
     *
     * @return array
     */
    public function getExtras(): array
    {
        return $this->extras;
    }

    /**
     * Converts the DTO to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'trip_type' => $this->tripType,
            'passengers' => $this->passengers,
            'segments' => $this->segments,
            'modifiers' => $this->modifiers,
            'pricing' => $this->pricing,
            'extras' => $this->extras,
        ];
    }

    /**
     * Serializes the DTO to JSON.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Checks if the search is for a direct flight only.
     *
     * @return bool
     */
    public function isDirectOnly(): bool
    {
        return $this->modifiers['directOnly'] ?? false;
    }

    /**
     * Gets the total number of passengers.
     *
     * @return int
     */
    public function getTotalPassengers(): int
    {
        return ($this->passengers['adults'] ?? 0) +
            ($this->passengers['children'] ?? 0) +
            ($this->passengers['infants'] ?? 0);
    }

    /**
     * Gets the departure airport code for the first segment.
     *
     * @return string|null
     */
    public function getDepartureAirport(): ?string
    {
        return $this->segments[0]['from'] ?? null;
    }

    /**
     * Gets the arrival airport code for the last segment.
     *
     * @return string|null
     */
    public function getArrivalAirport(): ?string
    {
        return end($this->segments)['to'] ?? null;
    }

    /**
     * Checks if flexible dates are enabled.
     *
     * @return bool
     */
    public function hasFlexibleDates(): bool
    {
        return $this->modifiers['flexDates'] ?? false;
    }

    /**
     * Merges additional data into the existing DTO and revalidates.
     *
     * @param array $additionalData Data to merge
     * @throws InvalidArgumentException If validation fails
     */
    public function mergeData(array $additionalData): void
    {
        $currentData = $this->toArray();
        $mergedData = array_replace_recursive($currentData, $additionalData);
        $this->validate($mergedData);
        $this->setProperties($mergedData);
    }
    private function generateSearchId(): string
    {
        return 'srch_'.Uuid::uuid4()->toString();
    }
    public function getSearchId(): string
    {
        return $this->searchId;
    }


}