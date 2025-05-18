<?php

namespace Redoy\FlyHub\Markup;

use Illuminate\Support\Facades\Log;

class MarkupManager
{
    // Apply markup to all flights in a provider's results
    public function applyMarkupToFlights(array $providerResults, string $provider): array
    {
        foreach ($providerResults as $key => $flight) {
            $providerResults[$key] = $this->applyMarkupToFlight($flight, $provider);
        }

        return $providerResults;
    }

    private function applyMarkupToFlight(array $flight, string $provider): array
    {
        $markupPercent = 10; // Example markup percentage

        if (!isset($flight['price']) || !is_array($flight['price'])) {
            return $flight; // No price to modify
        }

        // Preserve original price
        $flight['original_price'] = $flight['price'];

        // Extract price components
        $base = (float) ($flight['price']['base'] ?? 0);
        $totalTaxes = (float) ($flight['price']['total_taxes'] ?? 0);
        $totalFees = (float) ($flight['price']['total_fees'] ?? 0);
        $currency = $flight['price']['currency'] ;

        // Apply markup to base
        $markedUpBase = round($base * (1 + $markupPercent / 100), 2);
        $newTotalPrice = round($markedUpBase + $totalTaxes + $totalFees, 2);

        // Update the modified price
        $flight['price'] = [
            'currency' => $currency,
            'base' => number_format($markedUpBase, 2, '.', ''),
            'total_taxes' => number_format($totalTaxes, 2, '.', ''),
            'total_fees' => number_format($totalFees, 2, '.', ''),
            'total_price' => number_format($newTotalPrice, 2, '.', ''),
        ];

        return $flight;
    }
}
