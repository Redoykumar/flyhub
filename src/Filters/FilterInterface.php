<?php

namespace Redoy\FlyHub\Filters;

interface FilterInterface
{
    // Apply filter to an array of flights
    public function apply(array $flights): array;
}