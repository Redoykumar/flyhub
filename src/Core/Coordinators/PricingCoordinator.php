<?php

namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Core\FlyHubManager;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;

class PricingCoordinator
{
    protected $manager;

    public function __construct(FlyHubManager $manager)
    {
        $this->manager = $manager;
    }

    public function getPrice(PriceRequestDTO $request): PriceResponseDTO
    {
        // Get the provider name and config from FlyHubManager
        $providerName = config('flyhub.default_provider');
        $providerConfig = config("flyhub.providers.{$providerName}", []);

        if (empty($providerConfig)) {
            throw new \Exception("Configuration for provider {$providerName} not found.");
        }

        // Resolve the client and PriceService class from config
        $clientClass = $providerConfig['client'] ?? null;
        $priceServiceClass = $providerConfig['price'] ?? null;

        if (!class_exists($clientClass) || !class_exists($priceServiceClass)) {
            throw new \Exception("Client or price service class not found for provider {$providerName}.");
        }

        // Instantiate the client with config
        $client = new $clientClass($providerConfig);

        // Instantiate the PriceService with the client
        $priceService = new $priceServiceClass($client);

        // Fetch pricing data
        $priceResponse = $priceService->getPrice($request);

        return $priceResponse;
    }
}