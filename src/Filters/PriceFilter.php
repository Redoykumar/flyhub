<?php

namespace Redoy\FlyHub\Filters;

class PriceFilter implements FilterInterface
{
    protected $minPrice;
    protected $maxPrice;

    // Constructor sets price range
    public function __construct(array $priceRange)
    {
        $this->minPrice = $priceRange['min'] ?? 0;
        $this->maxPrice = $priceRange['max'] ?? PHP_INT_MAX;
    }

    // Filter flights by price range
    public function apply(array $flights):array
    {
        return array_filter($flights, function ($flight) {
            return $flight['price'] >= $this->minPrice && $flight['price'] <= $this->maxPrice;
        });
    }
}