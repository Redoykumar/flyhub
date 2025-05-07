<?php

namespace Redoy\FlyHub\Core;

use Illuminate\Http\Request;
use Redoy\FlyHub\Cache\PriceCache;
use Redoy\FlyHub\Cache\SearchCache;
use Illuminate\Support\Facades\Cache;
use Redoy\FlyHub\Cache\OfferIdentifiersCache;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\Core\Coordinators\SearchCoordinator;
use Redoy\FlyHub\Core\Coordinators\PricingCoordinator;

class FlyHubManager
{
    protected $providerClass;
    protected $config;
    protected $results;
    protected $meta;
    protected $priceCache;
    protected $searchCache;

    public function __construct($providerClass = null, array $config = [])
    {
        $this->providerClass = $providerClass ?: $this->getDefaultProviderClass();
        $this->config = $config ?: config('flyhub.providers.' . config('flyhub.default_provider'));
        $this->priceCache = new PriceCache();
        $this->searchCache = new SearchCache();
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

        // Check cache
        if ($this->searchCache->has($dto)) {
            $cachedResults = $this->searchCache->get($dto);
            $this->setResults($cachedResults['data'], $cachedResults['meta']);
            return $this;
        }

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

    public function price($request)
    {
        $dto = $request instanceof PriceRequestDTO
            ? $request
            : PriceRequestDTO::fromInput($request);

        if (empty($dto->offerId)) {
            throw new \Exception("Offer ID is required for pricing.");
        }

        // Check cache
        if ($this->priceCache->has($dto->offerId)) {
            $cachedData = $this->priceCache->get($dto->offerId);
            $this->results = [
                'totalPrice' => $cachedData['totalPrice'],
                'currency' => $cachedData['currency'],
                'flightSegments' => $cachedData['flightSegments'],
            ];
            $this->meta = $cachedData['meta'];
            return $this;
        }

        $coordinator = new PricingCoordinator($this);
        $priceResponse = $coordinator->getPrice($dto);

        // Apply pricing rules from config
        $providerName = config('flyhub.default_provider');
        $pricingRules = config("flyhub.pricing.providers.{$providerName}", []);
        $totalPrice = $priceResponse->totalPrice;

        // Apply markup and fixed fee
        $markupPercentage = $pricingRules['markup_percentage'] ?? 0;
        $fixedFee = $pricingRules['fixed_fee'] ?? 0;
        $discountPercentage = $pricingRules['discount_percentage'] ?? 0;

        $markupAmount = $totalPrice * ($markupPercentage / 100);
        $discountAmount = $totalPrice * ($discountPercentage / 100);
        $totalPrice = $totalPrice + $markupAmount + $fixedFee - $discountAmount;

        $this->results = [
            'totalPrice' => $totalPrice,
            'currency' => $priceResponse->currency,
            'flightSegments' => $priceResponse->flightSegments,
        ];
        $this->meta = array_merge($priceResponse->meta, [
            'pricing' => [
                'markup_percentage' => $markupPercentage,
                'fixed_fee' => $fixedFee,
                'discount_percentage' => $discountPercentage,
            ],
        ]);

        // Cache the results
        $this->priceCache->put($dto->offerId, [
            'totalPrice' => $totalPrice,
            'currency' => $priceResponse->currency,
            'flightSegments' => $priceResponse->flightSegments,
            'meta' => $this->meta,
        ]);

        return $this;
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