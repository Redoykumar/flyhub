<?php

namespace Redoy\FlyHub\Core;

use Redoy\FlyHub\Core\Coordinators\SearchCoordinator;

class FlyHubManager
{
    // Stores the provider class (e.g., TravelportClient or AmadeusClient)
    protected $providerClass;
    // Stores provider configuration from flyhub.php
    protected $config;
    // Stores search results from providers
    protected $results;

    // Constructor initializes with a provider class and config, or defaults
    public function __construct($providerClass = null, array $config = [])
    {
        $this->providerClass = $providerClass ?: $this->getDefaultProviderClass();
        $this->config = $config ?: config('flyhub.providers.' . config('flyhub.default_provider'));
    }

    // Sets a specific provider (e.g., 'travelport' or 'amadeus')
    public function provider($providerName)
    {
        $config = config("flyhub.providers.{$providerName}");
        $this->providerClass = $config['class'];
        $this->config = $config;
        return $this;
    }

    // Initiates a search by delegating to SearchCoordinator
    public function search($request)
    {
        $coordinator = new SearchCoordinator($this);
        return $coordinator->search($request);
    }

    // Getter for provider class
    public function getProviderClass()
    {
        return $this->providerClass;
    }

    // Getter for config
    public function getConfig()
    {
        return $this->config;
    }

    // Setter for results
    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

    // Getter for results
    public function getResults()
    {
        return $this->results ?? [];
    }

    // Fluent API methods for chaining
    public function orginalPrice()
    {
        return $this;
    } // Note: Typo 'orginal' from your route; consider fixing to 'originalPrice'
    public function price()
    {
        return $this;
    }
    public function display()
    {
        return $this->getResults();
    }
    public function filter()
    {
        return $this;
    }
    public function byPrice()
    {
        return $this;
    }
    public function byStops()
    {
        return $this;
    }
    public function byAirline()
    {
        return $this;
    }
    public function sort()
    {
        return $this;
    }
    public function byDuration()
    {
        return $this;
    }
    public function byDepartureTime()
    {
        return $this;
    }
    public function get()
    {
        return $this->getResults();
    }
    public function confirmPrice()
    {
        return $this;
    }
    public function reserve()
    {
        return $this;
    }
    public function withPassengerDetails($data)
    {
        return $this;
    }
    public function save()
    {
        return $this;
    }
    public function review()
    {
        return $this;
    }
    public function details()
    {
        return $this;
    }
    public function finalPrice()
    {
        return $this;
    }
    public function show()
    {
        return $this->getResults();
    }

    // Retrieves default provider class from config
    protected function getDefaultProviderClass(): string
    {
        return config('flyhub.providers.' . config('flyhub.default_provider') . '.class');
    }
}