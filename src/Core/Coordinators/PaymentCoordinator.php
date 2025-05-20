<?php
namespace Redoy\Flyhub\Core\Coordinators;


use Illuminate\Support\Facades\Log;
use Redoy\FlyHub\Cache\BookingCache;
use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\Flyhub\DTOs\Responses\PaymentResponseDTO;

class PaymentCoordinator
{


    public function processPayment(PaymentRequestDTO $dto): ?PaymentResponseDTO
    {
        $bookingCache = new BookingCache();
        $offerData = $bookingCache->get($dto->getBookingId());
        $providerConfig = config("flyhub.providers.{$offerData['provider']}", null);
        $client = new $providerConfig['client']($providerConfig);
        $bookService = new $providerConfig['payment']($client);
        return $bookService->processPayment($dto, $offerData);
    }
}