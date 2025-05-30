<?php

namespace Redoy\FlyHub\Sorters;

class PriceSorter implements SorterInterface
{
    // Sort flights by price (ascending)
    public function apply(array $flights): array
    {
        usort($flights, function ($a, $b) {
            return $a['price']['total_price'] <=> $b['price']['total_price'];
        });
        return $flights;
    }
}