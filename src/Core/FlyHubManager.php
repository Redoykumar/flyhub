<?php

namespace Redoy\FlyHub\Core;

use Redoy\FlyHub\Core\Coordinators\SearchCoordinator;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Illuminate\Http\Request;

class FlyHubManager
{
    protected $providerClass;
    protected $config;
    protected $results;

    public function __construct($providerClass = null, array $config = [])
    {
        $this->providerClass = $providerClass ?: $this->getDefaultProviderClass();
        $this->config = $config ?: config('flyhub.providers.' . config('flyhub.default_provider'));
    }

    public function provider($providerName)
    {
        $config = config("flyhub.providers.{$providerName}");
        $this->providerClass = $config['class'];
        $this->config = $config;
        return $this;
    }

    public function search($input)
    {
        $dto = $input instanceof SearchRequestDTO
            ? $input
            : new SearchRequestDTO(
                $input instanceof Request ? $input->all() : (is_array($input) ? $input : [])
            );
        $coordinator = new SearchCoordinator($this);
        return $coordinator->search($dto);
    }

    public function getProviderClass()
    {
        return $this->providerClass;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

    public function getResults()
    {
        return [
            'status' => 'success',
            'data' => $this->results ?? [],
            'errors' => []
        ];
    }

    public function orginalPrice()
    {
        return $this;
    }
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

    protected function getDefaultProviderClass(): string
    {
        return config('flyhub.providers.' . config('flyhub.default_provider') . '.class');
    }
}