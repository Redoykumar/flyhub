<?php

namespace Redoy\FlyHub\Contracts\Services;



use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PaymentResponseDTO;

interface PaymentServiceInterface
{
    /**
     * Process a payment based on the provided request data.
     *
     * @param PaymentRequestDTO $request The payment request data.
     * @return PaymentResponseDTO The payment response data.
     * @throws \FlyHub\Exceptions\FlyHubException If the payment process fails.
     */
    public function processPayment(PaymentRequestDTO $request, array $cache): PaymentResponseDTO;
}