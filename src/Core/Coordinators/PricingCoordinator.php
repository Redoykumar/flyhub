<?php
namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Cache\PriceCache;
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
        $offerData = $offers[$offerId];
        $providerConfig = config("flyhub.providers.{$offerData['provider']}", null);
        $client = new $providerConfig['client']($providerConfig);
        $priceService = new $providerConfig['price']($client);
        $priceCache = new PriceCache();
        $offerPrice=$priceService->price($offerData['offerRef']);
        $priceCache->put($offerPrice->getOfferIds()[0],$offerData);
        $priceCache->get($offerPrice->getOfferIds()[0]);

        return $offerPrice;
    }
}