<?php

namespace Redoy\FlyHub\Core\Coordinators;

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
        $bookService = new $providerConfig['book']($client);
        return $bookService->book($dto,$offerData['offerRef']);
    }
}
