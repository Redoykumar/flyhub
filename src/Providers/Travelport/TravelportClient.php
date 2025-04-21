<?php

namespace Redoy\FlyHub\Providers\Travelport;

class TravelportClient
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // Add methods to interact with Travelport API if needed
}