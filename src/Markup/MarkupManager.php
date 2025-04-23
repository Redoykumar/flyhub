<?php

namespace Redoy\FlyHub\Markup;

class MarkupManager
{
    // Apply markup to a single flight
    public function applyMarkup(array $flight, string $provider): array
    {
        $pricingSource = config('flyhub.pricing.source', 'config');
        $fareType = $this->normalizeFareType($flight['fare_type'] ?? 'economy');

        // Get pricing rules
        $rules = $this->getPricingRules($pricingSource, $fareType, $provider);

        // Store original price
        $flight['original_price'] = $flight['price'];
        $price = $flight['price'];

        // Apply fare class markup and fixed fee
        $fareMarkup = ($price * ($rules['fare']['markup_percentage'] / 100)) + $rules['fare']['fixed_fee'];
        $price += $fareMarkup;

        // Apply provider-specific markup and fixed fee
        $providerMarkup = ($price * ($rules['provider']['markup_percentage'] / 100)) + $rules['provider']['fixed_fee'];
        $price += $providerMarkup;

        // Apply discount
        $discount = $price * ($rules['provider']['discount_percentage'] / 100);
        $price -= $discount;

        // Ensure price is not negative
        $flight['final_price'] = max(0, round($price, 2));
        $flight['currency'] = config('flyhub.pricing.currency', 'USD');

        return $flight;
    }

    // Apply markup to all flights in a provider's results
    public function applyMarkupToFlights(array $providerResults, string $provider): array
    {
        $providerResults['flights'] = array_map(function ($flight) use ($provider) {
            return $this->applyMarkup($flight, $provider);
        }, $providerResults['flights']);
        return $providerResults;
    }

    // Get pricing rules based on source
    protected function getPricingRules(string $source, string $fareType, string $provider): array
    {
        if ($source === 'database') {
            // Placeholder for database logic (e.g., query a PricingRule model)
            // Return default config rules for now
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
        return 'economy'; // Default
    }
}