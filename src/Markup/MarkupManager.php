<?php

namespace Redoy\FlyHub\Markup;

use Illuminate\Support\Facades\Log;

class MarkupManager
{
    // Apply markup to a single flight
    public function applyMarkup(array $flight, string $provider): array
    {
        $pricingSource = config('flyhub.pricing.source', 'config');
        $targetCurrency = config('flyhub.pricing.currency', 'USD');
        $fareType = $this->normalizeFareType($flight['fare_type'] ?? 'economy');

        // Get pricing rules
        $rules = $this->getPricingRules($pricingSource, $fareType, $provider);

        // Validate price structure
        $price = 0.0;
        $basePrice = 0.0;
        $taxPrice = 0.0;
        $currentCurrency = 'USD';

        if (isset($flight['price_scalar']) && is_numeric($flight['price_scalar'])) {
            $price = (float) $flight['price_scalar'];
            $basePrice = $price; // Assume base = total if only scalar provided
            $flight['price'] = $flight['price'] ?? [
                'amount' => $price,
                'currency' => 'USD',
                'breakdown' => ['base' => $price, 'tax' => 0.0],
                'currency_conversion' => [],
            ];
        } elseif (isset($flight['price']) && is_array($flight['price']) && isset($flight['price']['amount']) && is_numeric($flight['price']['amount'])) {
            $price = (float) $flight['price']['amount'];
            $basePrice = (float) ($flight['price']['breakdown']['base'] ?? $price);
            $taxPrice = (float) ($flight['price']['breakdown']['tax'] ?? 0.0);
            $currentCurrency = $flight['price']['currency'] ?? 'USD';
        } else {
            Log::warning('Invalid price structure in flight data', ['flight' => $flight]);
            $flight['price'] = [
                'amount' => 0.0,
                'currency' => 'USD',
                'breakdown' => ['base' => 0.0, 'tax' => 0.0],
                'currency_conversion' => [],
            ];
        }

        // Handle currency conversion if needed
        if ($currentCurrency !== $targetCurrency) {
            $conversionRate = $this->getConversionRate($currentCurrency, $targetCurrency);
            $price *= $conversionRate;
            $basePrice *= $conversionRate;
            $taxPrice *= $conversionRate;
            $flight['price']['currency_conversion'] = [
                'from' => $currentCurrency,
                'to' => $targetCurrency,
                'rate' => $conversionRate,
            ];
            $flight['price']['currency'] = $targetCurrency;
        }

        // Store original price in breakdown
        $flight['price']['breakdown']['original_price'] = round($price, 2);

        // Apply fare class markup and fixed fee
        $fareMarkup = ($basePrice * ($rules['fare']['markup_percentage'] / 100)) + $rules['fare']['fixed_fee'];
        $basePrice += $fareMarkup;

        // Apply provider-specific markup and fixed fee
        $providerMarkup = ($basePrice * ($rules['provider']['markup_percentage'] / 100)) + $rules['provider']['fixed_fee'];
        $basePrice += $providerMarkup;

        // Apply discount
        $discount = ($basePrice * ($rules['provider']['discount_percentage'] / 100));
        $basePrice -= $discount;

        // Ensure base price is not negative
        $basePrice = max(0, round($basePrice, 2));

        // Update price structure
        $flight['price']['amount'] = $basePrice + $taxPrice;
        $flight['price']['breakdown']['base'] = $basePrice;
        $flight['price']['breakdown']['tax'] = $taxPrice;
        $flight['price']['breakdown']['final_price'] = round($flight['price']['amount'], 2);

        // Set scalar price for backward compatibility
        $flight['price_scalar'] = $flight['price']['amount'];

        return $flight;
    }

    // Apply markup to all flights in a provider's results
    public function applyMarkupToFlights(array $providerResults, string $provider): array
    {
        $providerResults = array_map(function ($flight) use ($provider) {
            return $this->applyMarkup($flight, $provider);
        }, $providerResults);
        return $providerResults;
    }

    // Get pricing rules based on source
    protected function getPricingRules(string $source, string $fareType, string $provider): array
    {
        if ($source === 'database') {
            // Placeholder for database logic
            return $this->getConfigRules($fareType, $provider);
        }

        return $this->getConfigRules($fareType, $provider);
    }

    // Get pricing rules from config
    protected function getConfigRules(string $fareType, string $provider): array
    {
        $fareRules = config("flyhub.pricing.rules.{$fareType}", [
            'markup_percentage' => 0,
            'fixed_fee' => 0,
            'discount_percentage' => 0,
        ]);

        $providerRules = config("flyhub.pricing.providers.{$provider}", [
            'markup_percentage' => 0,
            'fixed_fee' => 0,
            'discount_percentage' => 0,
        ]);

        return [
            'fare' => $fareRules,
            'provider' => $providerRules,
        ];
    }

    // Get currency conversion rate
    protected function getConversionRate(string $from, string $to): float
    {
        $rates = config('flyhub.pricing.conversion_rates', [
            'AUD' => ['USD' => 0.67, 'EUR' => 0.62],
            'USD' => ['EUR' => 0.92, 'AUD' => 1.49],
            'EUR' => ['USD' => 1.09, 'AUD' => 1.61],
        ]);

        return $rates[$from][$to] ?? 1.0; // Default to no conversion
    }

    // Normalize fare type to match config keys
    protected function normalizeFareType(string $fareType): string
    {
        $fareType = strtolower($fareType);
        if (str_contains($fareType, 'economy')) {
            return 'economy';
        } elseif (str_contains($fareType, 'business')) {
            return 'business';
        } elseif (str_contains($fareType, 'first')) {
            return 'first_class';
        }
        return 'economy';
    }
}