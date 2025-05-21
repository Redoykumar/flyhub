<?php

namespace Redoy\FlyHub\Core;

use Illuminate\Http\Request;
use Redoy\FlyHub\Cache\PriceCache;
use Redoy\FlyHub\Cache\SearchCache;
use Redoy\FlyHub\Cache\OfferIdentifiersCache;

use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Requests\BookingRequestDTO;


use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\FlyHub\Core\Coordinators\SearchCoordinator;
use Redoy\FlyHub\Core\Coordinators\BookingCoordinator;
use Redoy\Flyhub\Core\Coordinators\PaymentCoordinator;
use Redoy\FlyHub\Core\Coordinators\PricingCoordinator;


class FlyHubManager
{
    protected $providerClass;
    protected $config;
    protected $results;
    protected $meta;
    protected $priceCache;
    protected $searchCache;
    protected $offerIdentifiersCache;

    public function __construct($providerClass = null, array $config = [])
    {
        $this->providerClass = $providerClass ?: $this->getDefaultProviderClass();
        $this->config = $config ?: config('flyhub.providers.' . config('flyhub.default_provider'));
        $this->priceCache = new PriceCache();
        $this->searchCache = new SearchCache();
        $this->offerIdentifiersCache = new OfferIdentifiersCache();
    }

    public function provider($providerName)
    {
        $this->config = config("flyhub.providers.{$providerName}");
        $this->providerClass = $this->config['client'] ?? null;
        if (!$this->providerClass) {
            throw new \Exception("Client class not defined for provider {$providerName}.");
        }
        return $this;
    }

    public function search($input)
    {

        $dto = $input instanceof SearchRequestDTO
            ? $input
            : new SearchRequestDTO(
                $input instanceof Request ? $input->all() : (is_array($input) ? $input : [])
            );

        // // Check cache
        // if ($this->searchCache->has($dto)) {
        //     $cachedResults = $this->searchCache->get($dto);
        //     $this->setResults($cachedResults['data'], $cachedResults['meta']);
        //     return $this;
        // }

        $coordinator = new SearchCoordinator($this);
        $searchResponse = $coordinator->search($dto)->get();
        $searchResponse=array_merge($searchResponse,['meta'=> $this->generateMeta($dto)]);
        // Cache the results
        $this->searchCache->put($dto, $searchResponse);

        $this->setResults($searchResponse['data'], $searchResponse['meta']);
        return $this;
    }
    protected function generateMeta(SearchRequestDTO $dto ): array
    {
        return [
            'search_id' => $dto->getSearchId(),

        ];
    }

    public function price($input)
    {
        $dto = $input instanceof PriceRequestDTO
            ? $input
            : new PriceRequestDTO(
                $input instanceof Request ? $input->all() : (is_array($input) ? $input : [])
            );
        // Call PricingCoordinator with cached search result
        $coordinator = new PricingCoordinator();
        $priceResponse = $coordinator->price($dto);
        // Add meta
        $priceResponse = array_merge($priceResponse->toArray(), [
            'meta' => [
                'search_id' => $dto->getSearchId(),
            ],
        ]);

        // You may cache the price again if needed
        // $this->priceCache->put($dto, $priceResponse);

        $this->setResults($priceResponse['offers'], $priceResponse['meta']);
        return $this;
    }

    public function book($input)
    {
        // Step 1: Normalize input to BookingRequestDTO
        $dto = $input instanceof BookingRequestDTO
            ? $input
            : new BookingRequestDTO(
                $input instanceof Request ? $input->all() : (is_array($input) ? $input : [])
            );

        // Step 2: Pass DTO to BookingCoordinator
        $coordinator = new BookingCoordinator();

        // Step 3: Process the booking logic
        $result = $coordinator->book($dto);

        // Step 4: Return or format response
        return [
            'status' => 'success',
            'data' => $result->toArray(),
        ];
    }

    public function pay($input)
    {

            $dto = $input instanceof PaymentRequestDTO
                ? $input
                : new PaymentRequestDTO(
                    $input instanceof Request ? $input->all() : (is_array($input) ? $input : [])
                );

            $coordinator = new PaymentCoordinator();
            $result = $coordinator->processPayment($dto);

            return response()->json([
                'status' => 'success',
                'data' => $result->toArray(),
            ]);

    }

    public function getProviderClass()
    {
        return $this->providerClass;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setResults($results, $meta)
    {
        $this->results = $results;
        $this->meta = $meta;
        return $this;
    }

    public function getResults()
    {
        return [
            'status' => 'success',
            'data' => $this->results ?? [],
            'meta' => $this->meta ?? [],
            'errors' => [],
        ];
    }

    public function orginalPrice()
    {
        return $this;
    }
    public function display()
    {
        return $this->getResults();
    }
    public function filters()
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
        return config('flyhub.providers.' . config('flyhub.default_provider') . '.client');
    }
}