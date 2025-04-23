<?php

namespace Redoy\FlyHub\Sorters;

interface SorterInterface
{
    // Apply sorting to an array of flights
    public function apply(array $flights): array;
}