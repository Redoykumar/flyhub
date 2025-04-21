<?php

namespace Redoy\FlyHub\Contracts;

interface ProviderInterface
{
    public function search($request);

    public function book($request);

    public function confirmPrice($flightId);
}