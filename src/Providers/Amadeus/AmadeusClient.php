<?php

namespace Redoy\FlyHub\Providers\Amadeus;

class AmadeusClient
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // Add real API methods here later if needed
}