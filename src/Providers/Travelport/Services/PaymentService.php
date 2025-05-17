<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Illuminate\Http\Client\Response;
use FlyHub\DTOs\Responses\PaymentResponseDTO;
use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Contracts\Services\PaymentServiceInterface;
use Redoy\FlyHub\Providers\Travelport\Transformers\PaymentTransformer;

class PaymentService implements PaymentServiceInterface
{
    protected TravelportClient $client;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        // Step 1: Build reservation from existing PNR
        $reservationId = $this->buildReservationFromLocator($request->getPnr());
        dd($reservationId);
        if (!$reservationId) {
            return $this->failedResponse('Reservation build failed');
        }

        // Step 2: Add form of payment (e.g., credit card, cash)
        $formOfPaymentAdded = $this->addFormOfPayment($reservationId, $request->formOfPayment);
        if (!$formOfPaymentAdded) {
            return $this->failedResponse('Form of payment failed');
        }

        // Step 3: Add payment offer to reservation (e.g., accept price, taxes)
        $paymentOfferAdded = $this->addPaymentOffer($reservationId, $request->paymentOffer);
        if (!$paymentOfferAdded) {
            return $this->failedResponse('Payment offer failed');
        }

        // Step 4: Finalize the reservation (issue ticket, commit transaction)
        $finalized = $this->finalizeReservation($reservationId);
        if (!$finalized) {
            return $this->failedResponse('Finalizing reservation failed');
        }

        // Step 5: Fetch final reservation data after payment success
        $finalResponse = $this->getFinalReservation($reservationId);
        if (!$finalResponse) {
            return $this->failedResponse('Fetching final reservation failed');
        }

        // Step 6: Transform API response into standardized DTO response
        return $this->transformResponse($finalResponse);
    }

    /**
     * Step 1 - Call Travelport API to build reservation session from locator (PNR)
     */
    private function buildReservationFromLocator(string $pnr): ?string
    {
        $response = $this->client
            ->request('post', "/book/session/reservationworkbench/buildfromlocator?Locator={$pnr}")
            ->send();


        return $response->json()['ReservationResponse']['Reservation']['Identifier']['value'] ?? null;
    }

    /**
     * Step 2 - Add the payment method to the reservation (e.g. card, cash, voucher)
     */
    private function addFormOfPayment(string $reservationId, array $payload): bool
    {
        $response = $this->client
            ->request('post', "/payment/reservationworkbench/{$reservationId}/formofpayment")
            ->withBody($payload)
            ->send();

        return !$this->hasErrors($response);
    }

    /**
     * Step 3 - Attach a payment offer that links cost elements (fare, taxes, etc.)
     */
    private function addPaymentOffer(string $reservationId, array $payload): bool
    {
        $response = $this->client
            ->request('post', "/paymentoffer/reservationworkbench/{$reservationId}/payments")
            ->withBody($payload)
            ->send();

        return !$this->hasErrors($response);
    }

    /**
     * Step 4 - Finalize the reservation to commit booking and payment
     */
    private function finalizeReservation(string $reservationId): bool
    {
        $response = $this->client
            ->request('post', "/book/session/reservationworkbench/{$reservationId}")
            ->send();

        return !$this->hasErrors($response);
    }

    /**
     * Step 5 - Retrieve the finalized reservation from Travelport
     */
    private function getFinalReservation(string $reservationId): ?Response
    {
        $response = $this->client
            ->request('post', "/book/reservation/reservations/{$reservationId}")
            ->send();

        return $this->hasErrors($response) ? null : $response;
    }

    /**
     * Step 6 - Convert the raw Travelport response to a structured PaymentResponseDTO
     */
    private function transformResponse(Response $response): PaymentResponseDTO
    {
        return PaymentTransformer::transformToResponse($response->json());
    }

    /**
     * Helper - Check for API response errors
     */
    private function hasErrors(Response $response): bool
    {
        return $this->handleResponseErrors($response)->failed();
    }

    /**
     * Helper - Return failed DTO with error message
     */
    private function failedResponse(string $message): PaymentResponseDTO
    {
        return new PaymentResponseDTO([
            'success' => false,
            'message' => $message,
        ]);
    }
}
