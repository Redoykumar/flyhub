<?php
namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Cache\OfferIdentifiersCache;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;
use Redoy\FlyHub\Contracts\Services\PriceServiceInterface;

class PricingCoordinator
{
    protected $offerIdentifiersCache;

    public function __construct()
    {
        $this->offerIdentifiersCache = new OfferIdentifiersCache();
    }

    public function price(PriceRequestDTO $dto): PriceResponseDTO
    {
        $searchId = $dto->getSearchId();
        $offerId = $dto->getOfferId();
        $offers = $this->offerIdentifiersCache->get($searchId);
        if (!isset($offers[$offerId])) {
            throw new \Exception("Offer ID {$offerId} not found for search ID {$searchId}.");
        }

        $offerData = $offers[$offerId];
        
        if (!isset($offerData['provider'])) {
            throw new \Exception("Provider class not defined in offer data for offer ID {$offerId}.");
        }
        $providerConfig = config("flyhub.providers.{$offerData['provider']}", null);
        $client = new $providerConfig['client']($providerConfig);
        $priceService = new $providerConfig['price']($client);    
        return $priceService->price($offerData['offerRef']);
    }
}