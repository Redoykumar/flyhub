<?php

namespace FlyHub\Providers\Amadeus\Services;

use FlyHub\Contracts\Services\PaymentServiceInterface;
use FlyHub\DTOs\Requests\PaymentRequestDTO;
use FlyHub\DTOs\Responses\PaymentResponseDTO;
use FlyHub\Exceptions\FlyHubException;
use FlyHub\Providers\Amadeus\AmadeusClient;
use FlyHub\Providers\Amadeus\Transformers\PaymentTransformer;

class PaymentService implements PaymentServiceInterface
{
    protected $client;
    protected $transformer;

    public function __construct(AmadeusClient $client, PaymentTransformer $transformer)
    {
        $this->client = $client;
        $this->transformer = $transformer;
    }

    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        try {
            // Prepare request data for Amadeus API
            $apiRequestData = $this->preparePaymentRequest($request);

            // Call Amadeus API
            $rawResponse = $this->client->processPayment($apiRequestData);

            // Transform the response into a standardized DTO
            return $this->transformer->transform($rawResponse);
        } catch (\Exception $e) {
            throw new FlyHubException("Failed to process payment with Amadeus: " . $e->getMessage());
        }
    }

    protected function preparePaymentRequest(PaymentRequestDTO $request): array
    {
        // Map PaymentRequestDTO to Amadeus API format
        return [
            'data' => [
                'bookingId' => $request->bookingId,
                'payment' => [
                    'method' => $request->paymentMethod,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'details' => $request->paymentDetails,
                ],
            ],
        ];
    }
}