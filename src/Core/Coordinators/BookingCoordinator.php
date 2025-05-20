<?php

namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Cache\PriceCache;
use Redoy\FlyHub\Cache\BookingCache;
use Redoy\FlyHub\Cache\OfferIdentifiersCache;
use Redoy\FlyHub\DTOs\Requests\BookingRequestDTO;



class BookingCoordinator
{
    protected OfferIdentifiersCache $offerIdentifiersCache;

    public function __construct()
    {
        $this->offerIdentifiersCache = new OfferIdentifiersCache();
    }

    public function book(BookingRequestDTO $dto): mixed
    {
        $priceCache = new PriceCache();
        $offerData = $priceCache->get($dto->getPriceId());
        $providerConfig = config("flyhub.providers.{$offerData['provider']}", null);
        $client = new $providerConfig['client']($providerConfig);
        $bookService = new $providerConfig['book']($client);
        $bookingOffer =$bookService->book($dto,$offerData['offerRef']);
        $bookingCache = new BookingCache();
        $bookingCache->setCacheValue($bookingOffer->getCache());
        return $bookingOffer;
    }
}
