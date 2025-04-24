<?php

namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class SearchTransformer
{
    /**
     * Transform the Travelport response into the standard FlightSearchResponse format.
     *
     * @param array $data The raw Travelport API response
     * @param array|null $requestData The search request data (from DTO)
     * @return array
     */
    public static function transform(array $data, ?array $requestData = null): array
    {
        // Log the input for debugging
        Log::debug('Travelport SearchTransformer input', [
            'data' => json_encode($data, JSON_PRETTY_PRINT),
            'requestData' => $requestData,
        ]);

        // Validate the response structure
        if (!self::isValidResponse($data)) {
            Log::warning('Invalid or missing CatalogProductOfferings structure', ['data' => $data]);
            $flights = [self::buildDefaultFlight($requestData)];
            return self::buildResult($flights, $requestData);
        }

        try {
            $flights = self::processCatalogProductOfferings($data['CatalogProductOfferingsResponse']['CatalogProductOfferings'], $requestData);
            return self::buildResult($flights, $requestData);
        } catch (\Exception $e) {
            Log::error('SearchTransformer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $flights = [self::buildDefaultFlight($requestData)];
            return self::buildResult($flights, $requestData);
        }
    }

    /**
     * Check if the response has the expected structure.
     *
     * @param array $data The raw response
     * @return bool
     */
    private static function isValidResponse(array $data): bool
    {
        return isset($data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering']);
    }

    /**
     * Process CatalogProductOfferings to generate flights.
     *
     * @param array $catalog The CatalogProductOfferings data
     * @param array|null $requestData The search request data
     * @return array
     */
    private static function processCatalogProductOfferings(array $catalog, ?array $requestData): array
    {
        $flights = [];
        $offerings = (array) ($catalog['CatalogProductOffering'] ?? []);
        $offerings = is_array($offerings) ? $offerings : [$offerings];

        Log::debug('Processing CatalogProductOfferings', ['count' => count($offerings)]);

        foreach ($offerings as $offering) {
            $flight = self::buildFlightFromOffering($offering, $requestData);
            if ($flight) {
                $flights[] = $flight;
            }
        }

        return $flights;
    }

    /**
     * Build a flight from a CatalogProductOffering.
     *
     * @param array $offering The offering data
     * @param array|null $requestData The search request data
     * @return array|null
     */
    private static function buildFlightFromOffering(array $offering, ?array $requestData): ?array
    {
        $id = $offering['id'] ?? 'TRP_' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));
        $origin = $offering['Departure'] ?? $requestData['origin'] ?? 'DAC';
        $destination = $offering['Arrival'] ?? $requestData['destination'] ?? 'DXB';

        // Extract ProductBrandOptions and ProductBrandOffering
        $productBrandOptions = (array) ($offering['ProductBrandOptions'] ?? []);
        if (empty($productBrandOptions)) {
            Log::warning('No ProductBrandOptions found for offering', ['id' => $id]);
            return null;
        }

        // Use the first ProductBrandOffering for simplicity
        $productBrandOffering = (array) ($productBrandOptions[0]['ProductBrandOffering'][0] ?? []);
        if (empty($productBrandOffering)) {
            Log::warning('No ProductBrandOffering found for offering', ['id' => $id]);
            return null;
        }

        // Extract price details
        $priceInfo = $productBrandOffering['BestCombinablePrice'] ?? [];
        $price = isset($priceInfo['TotalPrice']) && is_numeric($priceInfo['TotalPrice']) ? (float) $priceInfo['TotalPrice'] : 376.2;
        $base = isset($priceInfo['Base']) && is_numeric($priceInfo['Base']) ? (float) $priceInfo['Base'] : $price * 0.813;
        $taxes = isset($priceInfo['TotalTaxes']) && is_numeric($priceInfo['TotalTaxes']) ? (float) $priceInfo['TotalTaxes'] : $price * 0.187;

        // Extract brand reference for fare type
        $brandRef = $productBrandOffering['Brand']['BrandRef'] ?? 'b0';
        $fareType = self::normalizeFareType($brandRef);

        // Build segments (placeholders for now)
        $segments = [self::buildSegment($origin, $destination, $fareType)];

        // Build the flight object
        return [
            'id' => $id,
            'provider' => 'travelport',
            'fare_type' => $fareType,
            'total_duration' => 'PT8H30M',  // Placeholder
            'stops' => 1,  // Assume 1 stop for now
            'price' => [
                'amount' => round($price, 2),
                'currency' => 'AUD',
                'breakdown' => [
                    'base' => round($base, 2),
                    'tax' => round($taxes, 2),
                ],
                'currency_conversion' => [
                    'from' => 'USD',
                    'to' => 'EUR',
                    'rate' => 0.92,
                ],
            ],
            'segments' => $segments,
            'conditions' => self::buildConditions(),
            'in_flight_amenities' => self::buildAmenities(),
            'availability' => self::buildAvailability(),
            'airline_contact' => self::buildAirlineContact(),
            'booking_token' => 'ABC' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 3)) . '_SECURETOKEN',
        ];
    }

    /**
     * Build a segment for the flight (placeholders for now).
     *
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $fareType Fare type (e.g., 'Economy')
     * @return array
     */
    private static function buildSegment(string $origin, string $destination, string $fareType): array
    {
        return [
            'segment_number' => 1,
            'from' => [
                'airport' => $origin,
                'city' => 'Dhaka',  // Placeholder
                'country' => 'BD',   // Placeholder
                'time' => '2025-06-01T09:00:00Z',  // Placeholder
            ],
            'to' => [
                'airport' => $destination,
                'city' => 'Dubai',  // Placeholder
                'country' => 'AE',   // Placeholder
                'time' => '2025-06-01T13:30:00Z',  // Placeholder
            ],
            'flight_number' => 'EK583',  // Placeholder
            'airline' => [
                'code' => 'EK',  // Placeholder
                'name' => 'Emirates',  // Placeholder
                'icon' => 'https://cdn.example.com/airlines/EK.png',  // Placeholder
            ],
            'aircraft' => 'Boeing 777',  // Placeholder
            'duration' => 'PT4H30M',  // Placeholder
            'flight_class' => $fareType,
            'cabin_type' => 'Standard',  // Placeholder
            'layover' => [
                'duration' => 'PT1H30M',  // Placeholder
                'location' => 'Doha',  // Placeholder
            ],
        ];
    }

    /**
     * Build static conditions for the flight.
     *
     * @return array
     */
    private static function buildConditions(): array
    {
        return [
            'is_refundable' => true,
            'baggage' => [
                'checked' => '30kg',
                'carry_on' => '7kg',
            ],
            'terms_and_conditions' => [
                'cancellation_policy' => 'https://example.com/policies/cancel',
                'change_policy' => 'https://example.com/policies/change',
            ],
        ];
    }

    /**
     * Build static in-flight amenities.
     *
     * @return array
     */
    private static function buildAmenities(): array
    {
        return [
            'wifi' => true,
            'meal' => 'Vegetarian',
            'entertainment' => true,
        ];
    }

    /**
     * Build static availability data.
     *
     * @return array
     */
    private static function buildAvailability(): array
    {
        return [
            'seats_remaining' => 12,
            'quota' => 'Limited',
        ];
    }

    /**
     * Build static airline contact information.
     *
     * @return array
     */
    private static function buildAirlineContact(): array
    {
        return [
            'phone' => '+123456789',
            'email' => 'support@emirates.com',
        ];
    }

    /**
     * Build a default flight for invalid responses.
     *
     * @param array|null $requestData The search request data
     * @return array
     */
    private static function buildDefaultFlight(?array $requestData): array
    {
        $flightId = 'FLT_TRP_' . now()->format('Ymd') . '_DEFAULT';
        $origin = $requestData['origin'] ?? 'DAC';
        $destination = $requestData['destination'] ?? 'DXB';

        Log::info('Generating default flight due to invalid response', [
            'flightId' => $flightId,
            'origin' => $origin,
            'destination' => $destination,
        ]);

        return [
            'id' => $flightId,
            'provider' => 'travelport',
            'fare_type' => 'ECONOMY',
            'total_duration' => 'PT8H30M',
            'stops' => 1,
            'price' => [
                'amount' => 376.2,
                'currency' => 'AUD',
                'breakdown' => [
                    'base' => 306.0,
                    'tax' => 70.2,
                ],
                'currency_conversion' => [
                    'from' => 'USD',
                    'to' => 'EUR',
                    'rate' => 0.92,
                ],
            ],
            'segments' => [
                self::buildSegment($origin, $destination, 'economy'),
            ],
            'conditions' => self::buildConditions(),
            'in_flight_amenities' => self::buildAmenities(),
            'availability' => self::buildAvailability(),
            'airline_contact' => self::buildAirlineContact(),
            'booking_token' => 'ABC_DEFAULT_SECURETOKEN',
        ];
    }

    /**
     * Build the final result array.
     *
     * @param array $flights The generated flights
     * @param array|null $requestData The search request data
     * @return array
     */
    private static function buildResult(array $flights, ?array $requestData): array
    {
        return [
            'provider' => 'Travelport',
            'flights' => $flights,
            'meta' => self::buildMeta($flights, $requestData),
        ];
    }

    /**
     * Build meta data for the response.
     *
     * @param array $flights The generated flights
     * @param array|null $requestData The search request data
     * @return array
     */
    private static function buildMeta(array $flights, ?array $requestData): array
    {
        $meta = [
            'search_id' => 'SRCH_' . now()->format('Ymd') . '_' . sprintf('%04d', rand(1, 9999)),
            'origin' => !empty($flights) ? $flights[0]['segments'][0]['from']['airport'] : ($requestData['origin'] ?? 'DAC'),
            'destination' => !empty($flights) ? $flights[0]['segments'][0]['to']['airport'] : ($requestData['destination'] ?? 'DXB'),
            'departure_date' => $requestData['departure_date'] ?? '2025-06-01',
            'round_trip' => $requestData['round_trip'] ?? false,
            'currency' => 'AUD',
            'total_results' => count($flights),
        ];

        Log::debug('Travelport meta data', ['meta' => $meta]);
        return $meta;
    }

    /**
     * Normalize fare type based on brand reference.
     *
     * @param string $brandRef The brand reference
     * @return string
     */
    private static function normalizeFareType(string $brandRef): string
    {
        $brandMap = [
            'b0' => 'ECONOMY',
            'b1' => 'BUSINESS',
            'b2' => 'FIRST_CLASS',
        ];

        return $brandMap[$brandRef] ?? 'ECONOMY';
    }
}