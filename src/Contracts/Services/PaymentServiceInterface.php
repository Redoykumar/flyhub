<?php

namespace Redoy\FlyHub\Contracts\Services;


use FlyHub\DTOs\Responses\PaymentResponseDTO;
use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;

interface PaymentServiceInterface
{
    /**
     * Process a payment based on the provided request data.
     *
     * @param PaymentRequestDTO $request The payment request data.
     * @return PaymentResponseDTO The payment response data.
     * @throws \FlyHub\Exceptions\FlyHubException If the payment process fails.
     */
    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO;
}