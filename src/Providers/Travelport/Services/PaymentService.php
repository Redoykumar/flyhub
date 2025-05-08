<?php

namespace FlyHub\Providers\Travelport\Services;

use FlyHub\Contracts\Services\PaymentServiceInterface;
use FlyHub\DTOs\Requests\PaymentRequestDTO;
use FlyHub\DTOs\Responses\PaymentResponseDTO;
use FlyHub\Exceptions\FlyHubException;
use FlyHub\Providers\Travelport\TravelportClient;
use FlyHub\Providers\Travelport\Transformers\PaymentTransformer;

class PaymentService implements PaymentServiceInterface
{
    protected $client;
    protected $transformer;

    public function __construct(TravelportClient $client, PaymentTransformer $transformer)
    {
        $this->client = $client;
        $this->transformer = $transformer;
    }

    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        try {
            // Prepare request data for Travelport API
            $apiRequestData = $this->preparePaymentRequest($request);

            // Call Travelport API
            $rawResponse = $this->client->processPayment($apiRequestData);

            // Transform the response into a standardized DTO
            return $this->transformer->transform($rawResponse);
        } catch (\Exception $e) {
            throw new FlyHubException("Failed to process payment with Travelport: " . $e->getMessage());
        }
    }

    protected function preparePaymentRequest(PaymentRequestDTO $request): array
    {
        // Map PaymentRequestDTO to Travelport API format
        return [
            'Payment' => [
                'BookingRef' => $request->bookingId,
                'PaymentMethod' => $request->paymentMethod,
                'Amount' => $request->amount,
                'CurrencyCode' => $request->currency,
                'PaymentDetails' => $request->paymentDetails,
            ],
        ];
    }
}