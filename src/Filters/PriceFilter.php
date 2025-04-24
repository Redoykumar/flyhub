<?php

namespace Redoy\FlyHub\Filters;

use Illuminate\Support\Facades\Log;

class PriceFilter implements FilterInterface
{
    protected $minPrice;
    protected $maxPrice;

    // Constructor sets price range
    public function __construct(array $priceRange)
    {
        $this->minPrice = isset($priceRange['min']) && is_numeric($priceRange['min']) ? (float) $priceRange['min'] : 0;
        $this->maxPrice = isset($priceRange['max']) && is_numeric($priceRange['max']) ? (float) $priceRange['max'] : PHP_INT_MAX;
    }

    // Filter flights by price range
    public function apply(array $flights): array
    {
        if (empty($flights)) {
            Log::debug('PriceFilter: No flights to filter', ['minPrice' => $this->minPrice, 'maxPrice' => $this->maxPrice]);
            return [];
        }

        return array_filter($flights, function ($flight) {
            // Validate flight and price structure
            if (!is_array($flight) || !isset($flight['price'])) {
                Log::warning('PriceFilter: Invalid flight structure, skipping', ['flight' => $flight]);
                return false;
            }

            // Prefer price['amount'], fallback to price_scalar
            $price = null;
            if (is_array($flight['price']) && isset($flight['price']['amount']) && is_numeric($flight['price']['amount'])) {
                $price = (float) $flight['price']['amount'];
            } elseif (isset($flight['price_scalar']) && is_numeric($flight['price_scalar'])) {
                $price = (float) $flight['price_scalar'];
            }

            if ($price === null) {
                Log::warning('PriceFilter: Invalid price in flight, skipping', ['flight' => $flight]);
                return false;
            }

            $withinRange = $price >= $this->minPrice && $price <= $this->maxPrice;
            Log::debug('PriceFilter: Checking flight price', [
                'price' => $price,
                'minPrice' => $this->minPrice,
                'maxPrice' => $this->maxPrice,
                'withinRange' => $withinRange,
                'flightId' => $flight['id'] ?? 'unknown',
            ]);

            return $withinRange;
        });
    }
}