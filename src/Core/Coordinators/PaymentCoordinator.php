<?php
namespace Redoy\Flyhub\Core\Coordinators;


use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\Flyhub\DTOs\Responses\PaymentResponseDTO;
use Illuminate\Support\Facades\Log;

class PaymentCoordinator
{


    public function processPayment(PaymentRequestDTO $dto): ?PaymentResponseDTO
    {
;

        $offerData['provider'] = 'travelport';
        if (!isset($offerData['provider'])) {
            throw new \Exception("Provider class not defined in offer data for offer ID .");
        }
        $providerConfig = config("flyhub.providers.{$offerData['provider']}", null);
        $client = new $providerConfig['client']($providerConfig);
        $bookService = new $providerConfig['payment']($client);
        return $bookService->processPayment($dto);
    }
}