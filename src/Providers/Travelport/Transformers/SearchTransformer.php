<?php

namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Illuminate\Support\Facades\Log;

class SearchTransformer
{
    public static function transform($data): array
    {
        $flights = [];
        $metadata = [];

        // Validate input
        if (!is_array($data) || !isset($data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'])) {
            Log::warning('Invalid Travelport response structure', ['data' => $data]);
            return ['flights' => [], 'metadata' => []];
        }

        $response = $data['CatalogProductOfferingsResponse'];
        $catalog = $response['CatalogProductOfferings'];
        $offerings = $catalog['CatalogProductOffering'];

        // Extract metadata
        $metadata = [
            'transaction_id' => $response['transactionId'] ?? null,
            'identifier' => $catalog['Identifier']['value'] ?? null,
        ];

        foreach ($offerings as $offering) {
            $itineraryId = $offering['id'] ?? null;
            $origin = $offering['Departure'] ?? '';
            $destination = $offering['Arrival'] ?? '';

            if (empty($origin) || empty($destination) || empty($itineraryId)) {
                Log::warning('Missing itinerary details', ['offering' => $offering]);
                continue;
            }

            $flight = [
                'itinerary_id' => $itineraryId,
                'provider' => 'Travelport',
                'origin' => $origin,
                'destination' => $destination,
                'segments' => [],
                'stops' => 0,
                'fare_options' => [],
            ];

            foreach ($offering['ProductBrandOptions'] as $option) {
                $flightRefs = $option['flightRefs'] ?? [];
                $flight['stops'] = count($flightRefs) > 1 ? count($flightRefs) - 1 : 0;

                // Build segments (no segment data, use placeholders)
                $segments = [];
                foreach ($flightRefs as $flightRef) {
                    $segments[] = [
                        'flight_ref' => $flightRef,
                        'departure_airport' => $origin,
                        'arrival_airport' => $destination,
                        'departure_time' => null, // Requires segment data
                        'arrival_time' => null,
                        'airline' => null,
                    ];
                }
                $flight['segments'] = $segments;

                foreach ($option['ProductBrandOffering'] as $brandOffering) {
                    $brandRef = $brandOffering['Brand']['BrandRef'] ?? 'b0';
                    $priceInfo = $brandOffering['BestCombinablePrice'] ?? [];
                    $price = isset($priceInfo['TotalPrice']) ? (float) $priceInfo['TotalPrice'] : 0.0;

                    if ($price <= 0) {
                        Log::warning('Invalid price in brand offering', ['brandOffering' => $brandOffering]);
                        continue;
                    }

                    $fareOption = [
                        'fare_type' => self::normalizeFareType($brandRef),
                        'brand_ref' => $brandRef,
                        'price' => [
                            'base' => isset($priceInfo['Base']) ? (float) $priceInfo['Base'] : 0.0,
                            'taxes' => isset($priceInfo['TotalTaxes']) ? (float) $priceInfo['TotalTaxes'] : 0.0,
                            'total' => $price,
                            'currency' => $priceInfo['CurrencyCode']['value'] ?? 'AUD',
                        ],
                        'product_ref' => $brandOffering['Product'][0]['productRef'] ?? null,
                        'terms_ref' => $brandOffering['TermsAndConditions']['termsAndConditionsRef'] ?? null,
                    ];

                    $flight['fare_options'][] = $fareOption;
                }
            }

            if (!empty($flight['fare_options'])) {
                $flights[] = $flight;
            }
        }

        return [
            'flights' => $flights,
            'metadata' => $metadata,
        ];
    }

    protected static function normalizeFareType(string $brandRef): string
    {
        $brandMap = [
            'b0' => 'economy',
            'b1' => 'business',
            'b2' => 'first_class',
        ];

        return $brandMap[$brandRef] ?? 'economy';
    }
}