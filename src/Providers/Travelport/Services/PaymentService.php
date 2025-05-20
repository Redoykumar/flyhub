<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Illuminate\Http\Client\Response;

use Redoy\Flyhub\DTOs\Requests\PaymentRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PaymentResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Contracts\Services\PaymentServiceInterface;
use Redoy\FlyHub\Providers\Travelport\Transformers\PaymentTransformer;

class PaymentService implements PaymentServiceInterface
{
    protected TravelportClient $client;
    private string $reservationWorkbenchId;
    private string $currencyCode = 'BDT';
    private string $totalPrice;
    private string $addFOPIdentifierValue;
    private string $reservationResponseReservationOfferIdentifierId;
    private string $reservationResponseReservationOfferIdentifierAuthority;
    private string $reservationResponseReservationOfferIdentifierValue;
    private array $formOfPaymentBody;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
        $this->formOfPaymentBody = [
            "FormOfPaymentCash" => [
                "id" => "formOfPayment_1",
                "FormOfPaymentRef" => "formOfPayment_1",
                "Identifier" => [
                    "authority" => "Travelport",
                    "value" => "A0656EFF-FAF4-456F-B061-0161008D6FOP",
                ],
            ],
        ];
    }

    public function processPayment(PaymentRequestDTO $request, array $cache): PaymentResponseDTO
    {
        // Step 1: Build reservation from existing PNR
        $reservation = $this->buildReservationFromLocator($cache['pnr']);
        $this->setReservationData($reservation, $cache['price']);
        // Step 2: Add form of payment (e.g., credit card, cash)
        $formOfPaymentAdded = $this->addFormOfPayment($this->reservationWorkbenchId, $this->formOfPaymentBody);
        $this->addFOPIdentifierValue = $formOfPaymentAdded;


        // Step 3: Add payment offer to reservation (e.g., accept price, taxes)
        $paymentOfferAdded = $this->addPaymentOffer($this->reservationWorkbenchId, $this->buildPaymentPayload());


        // Step 4: Finalize the reservation (issue ticket, commit transaction)
        $finalized = $this->finalizeReservation($this->reservationWorkbenchId);



        // Step 5: Fetch final reservation data after payment success
        $finalResponse = $this->getFinalReservation($this->reservationWorkbenchId);

        // $finalResponse = [
        //     "ReservationResponse" => [
        //         "@type" => "ReservationResponse",
        //         "Reservation" => [
        //             "@type" => "Reservation",
        //             "Offer" => [
        //                 [
        //                     "@type" => "Offer",
        //                     "id" => "offer_1",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "db2437be-ef83-4602-af8d-d75f5adcb271",
        //                     ],
        //                     "Product" => [
        //                         [
        //                             "@type" => "ProductAir",
        //                             "id" => "product_1",
        //                             "FlightSegment" => [
        //                                 [
        //                                     "@type" => "FlightSegment",
        //                                     "id" => "FlightSegment_01",
        //                                     "sequence" => 1,
        //                                     "connectionDuration" => "PT13H38M",
        //                                     "boundFlightsInd" => true,
        //                                     "Flight" => [
        //                                         "@type" => "Flight",
        //                                         "duration" => "PT3H7M",
        //                                         "carrier" => "UA",
        //                                         "number" => "2408",
        //                                         "equipment" => "777",
        //                                         "id" => "Flight_01",
        //                                         "Departure" => [
        //                                             "@type" => "Departure",
        //                                             "location" => "DEN",
        //                                             "date" => "2024-01-14",
        //                                             "time" => "17:50:00",
        //                                         ],
        //                                         "Arrival" => [
        //                                             "@type" => "Arrival",
        //                                             "location" => "IAD",
        //                                             "date" => "2024-01-14",
        //                                             "time" => "22:57:00",
        //                                         ],
        //                                     ],
        //                                 ],
        //                                 [
        //                                     "@type" => "FlightSegment",
        //                                     "id" => "FlightSegment_02",
        //                                     "sequence" => 2,
        //                                     "Flight" => [
        //                                         "@type" => "Flight",
        //                                         "duration" => "PT2H3M",
        //                                         "carrier" => "UA",
        //                                         "number" => "1940",
        //                                         "equipment" => "319",
        //                                         "id" => "Flight_02",
        //                                         "Departure" => [
        //                                             "@type" => "Departure",
        //                                             "location" => "IAD",
        //                                             "date" => "2024-01-15",
        //                                             "time" => "12:35:00",
        //                                         ],
        //                                         "Arrival" => [
        //                                             "@type" => "Arrival",
        //                                             "location" => "ATL",
        //                                             "date" => "2024-01-15",
        //                                             "time" => "14:38:00",
        //                                         ],
        //                                     ],
        //                                 ],
        //                             ],
        //                             "PassengerFlight" => [
        //                                 [
        //                                     "@type" => "PassengerFlight",
        //                                     "passengerQuantity" => 1,
        //                                     "passengerTypeCode" => "ADT",
        //                                     "FlightProduct" => [
        //                                         [
        //                                             "@type" => "FlightProduct",
        //                                             "segmentSequence" => [1],
        //                                             "classOfService" => "K",
        //                                             "cabin" => "Economy",
        //                                         ],
        //                                         [
        //                                             "@type" => "FlightProduct",
        //                                             "segmentSequence" => [2],
        //                                             "classOfService" => "L",
        //                                             "cabin" => "Economy",
        //                                         ],
        //                                     ],
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                     "Price" => [
        //                         "@type" => "PriceDetail",
        //                         "id" => "PriceDetail_1",
        //                         "CurrencyCode" => ["value" => "AUD"],
        //                         "Base" => 387,
        //                         "TotalTaxes" => 76.3,
        //                         "TotalFees" => 0,
        //                         "TotalPrice" => 463.3,
        //                         "PriceBreakdown" => [
        //                             [
        //                                 "@type" => "PriceBreakdownAir",
        //                                 "quantity" => 1,
        //                                 "requestedPassengerType" => "ADT",
        //                                 "Amount" => [
        //                                     "@type" => "Amount",
        //                                     "CurrencyCode" => [
        //                                         "decimalPlace" => 2,
        //                                         "value" => "USD",
        //                                     ],
        //                                     "Base" => 244.65,
        //                                 ],
        //                             ],
        //                             [
        //                                 "@type" => "PriceBreakdownAir",
        //                                 "quantity" => 1,
        //                                 "requestedPassengerType" => "ADT",
        //                                 "Amount" => [
        //                                     "@type" => "Amount",
        //                                     "currencySource" => "Charged",
        //                                     "approximateInd" => true,
        //                                     "CurrencyCode" => [
        //                                         "decimalPlace" => 2,
        //                                         "value" => "AUD",
        //                                     ],
        //                                     "Base" => 387,
        //                                     "Taxes" => [
        //                                         "@type" => "TaxesDetail",
        //                                         "TotalTaxes" => 76.3,
        //                                         "Tax" => [
        //                                             [
        //                                                 "currencyCode" => "AUD",
        //                                                 "taxCode" => "AY",
        //                                                 "value" => 17.8,
        //                                             ],
        //                                             [
        //                                                 "currencyCode" => "AUD",
        //                                                 "taxCode" => "US",
        //                                                 "value" => 29.1,
        //                                             ],
        //                                             [
        //                                                 "currencyCode" => "AUD",
        //                                                 "taxCode" => "XF",
        //                                                 "value" => 14.2,
        //                                             ],
        //                                             [
        //                                                 "currencyCode" => "AUD",
        //                                                 "taxCode" => "ZP",
        //                                                 "value" => 15.2,
        //                                             ],
        //                                         ],
        //                                     ],
        //                                     "Total" => 463.3,
        //                                 ],
        //                                 "FiledAmount" => [
        //                                     "currencyCode" => "USD",
        //                                     "decimalPlace" => 2,
        //                                     "value" => 244.65,
        //                                 ],
        //                                 "FareCalculation" =>
        //                                     "DEN UA WAS 129.30KAA2AWDN UA ATL 115.35LAA2ADDN USD244.65END",
        //                             ],
        //                         ],
        //                     ],
        //                     "TermsAndConditionsFull" => [
        //                         [
        //                             "@type" => "TermsAndConditionsFullAir",
        //                             "Identifier" => [
        //                                 "authority" => "Travelport",
        //                                 "value" =>
        //                                     "ec1ad4fa-3dbf-4e63-b9a1-86ec76ec030a",
        //                             ],
        //                             "ExpiryDate" => "2023-12-15T23:59:00Z",
        //                             "PaymentTimeLimit" => "2023-12-16T23:59:00Z",
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //             "Traveler" => [
        //                 [
        //                     "@type" => "Traveler",
        //                     "birthDate" => "1986-11-11",
        //                     "gender" => "Male",
        //                     "passengerTypeCode" => "ADT",
        //                     "id" => "travelerRefId_1",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "c6d27889-d6ae-4166-b6da-51af0515bd57",
        //                     ],
        //                     "PersonName" => [
        //                         "@type" => "PersonName",
        //                         "Given" => "TESTFIRST",
        //                         "Surname" => "TESTLAST",
        //                     ],
        //                     "Telephone" => [
        //                         [
        //                             "@type" => "TelephoneDetail",
        //                             "countryAccessCode" => "1",
        //                             "phoneNumber" => "212456121",
        //                             "id" => "telephone_1",
        //                             "cityCode" => "ORD",
        //                             "role" => "Home",
        //                         ],
        //                     ],
        //                     "Email" => [["value" => "TravelerOne@gmail.com"]],
        //                     "TravelDocument" => [
        //                         [
        //                             "@type" => "TravelDocumentDetail",
        //                             "docNumber" => "A123123",
        //                             "docType" => "Passport",
        //                             "expireDate" => "2033-12-14",
        //                             "issueCountry" => "US",
        //                             "birthDate" => "1986-11-11",
        //                             "Gender" => "Male",
        //                             "PersonName" => [
        //                                 "@type" => "PersonName",
        //                                 "Given" => "TESTFIRST",
        //                                 "Surname" => "TESTLAST",
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //             "FormOfPayment" => [
        //                 [
        //                     "@type" => "FormOfPaymentCash",
        //                     "id" => "formOfPayment_1",
        //                     "FormOfPaymentRef" => "formOfPayment_1",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "4388d7ae-f489-42d0-b09e-b4aa96eb5f6e",
        //                     ],
        //                 ],
        //             ],
        //             "Payment" => [
        //                 [
        //                     "@type" => "Payment",
        //                     "id" => "payment_1",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "5b14c504-1466-4cdf-885b-d271e0f21275",
        //                     ],
        //                     "Amount" => [
        //                         "code" => "USD",
        //                         "minorUnit" => 2,
        //                         "currencySource" => "Charged",
        //                         "approximateInd" => true,
        //                         "value" => 463.3,
        //                     ],
        //                     "FormOfPaymentIdentifier" => [
        //                         "FormOfPaymentRef" => "formOfPayment_1",
        //                         "Identifier" => [
        //                             "authority" => "Travelport",
        //                             "value" => "0bb68f4c-f136-4e4b-a8c0-9446cdbc1b1c",
        //                         ],
        //                     ],
        //                     "OfferIdentifier" => [
        //                         [
        //                             "id" => "offer_1",
        //                             "offerRef" => "offer_1",
        //                             "Identifier" => [
        //                                 "authority" => "Travelport",
        //                                 "value" =>
        //                                     "53f76dea-8344-45bc-a211-5906fa785d6e",
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //             "Receipt" => [
        //                 [
        //                     "@type" => "ReceiptConfirmation",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "190bc6b1-2bc1-4b00-bc43-4c0fe3f4784f",
        //                     ],
        //                     "Confirmation" => [
        //                         "@type" => "ConfirmationHold",
        //                         "Locator" => [
        //                             "source" => "1G",
        //                             "creationDate" => "2023-12-14",
        //                             "value" => "61K2S2",
        //                         ],
        //                         "OfferStatus" => [
        //                             "@type" => "OfferStatusAir",
        //                             "StatusAir" => [
        //                                 [
        //                                     "flightRefs" => ["Flight_01", "Flight_02"],
        //                                     "code" => "HK",
        //                                     "value" => "Confirmed",
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //                 [
        //                     "@type" => "ReceiptConfirmation",
        //                     "Identifier" => [
        //                         "authority" => "Travelport",
        //                         "value" => "4863b94f-d3cd-4312-9d79-be59c5bd9af3",
        //                     ],
        //                     "Confirmation" => [
        //                         "@type" => "ConfirmationHold",
        //                         "Locator" => ["source" => "UA", "value" => "E9ZHFW"],
        //                     ],
        //                 ],
        //                 [
        //                     "@type" => "ReceiptPayment",
        //                     "Document" => [
        //                         [
        //                             "@type" => "DocumentTicket",
        //                             "Number" => "0169904912054",
        //                             "TravelerIdentifierRef" => [
        //                                 "id" => "travelerRefId_1",
        //                                 "value" =>
        //                                     "81677d4c-a916-4a44-9ef6-9b2ebef6e3fb",
        //                             ],
        //                             "Amount" => ["@type" => "Amount", "Total" => 463.3],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //             "ReservationDisplaySequence" => [
        //                 "@type" => "ReservationDisplaySequence",
        //                 "DisplaySequence" => [
        //                     [
        //                         "@type" => "DisplaySequence",
        //                         "displaySequence" => 1,
        //                         "OfferRef" => "offer_1",
        //                         "ProductRef" => "product_1",
        //                         "Sequence" => 1,
        //                     ],
        //                     [
        //                         "@type" => "DisplaySequence",
        //                         "displaySequence" => 2,
        //                         "OfferRef" => "offer_1",
        //                         "ProductRef" => "product_1",
        //                         "Sequence" => 2,
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ],
        // ];


        // Step 6: Transform API response into standardized DTO response
        $paymentResponse = (new PaymentTransformer($finalResponse, $request))->transform();
        return $paymentResponse;
    }

    /**
     * Step 1 - Call Travelport API to build reservation session from locator (PNR)
     */
    private function buildReservationFromLocator(string $pnr): ?array
    {
        $response = $this->client
            ->request('post', "/book/session/reservationworkbench/buildfromlocator")
            ->withParams([
                'Locator' => $pnr
            ])
            ->send();


        return $response->json() ?? null;
    }

    /**
     * Step 2 - Add the payment method to the reservation (e.g. card, cash, voucher)
     */
    private function addFormOfPayment(string $reservationId, array $payload): ?string
    {
        $response = $this->client
            ->request('post', "/payment/reservationworkbench/{$reservationId}/formofpayment")
            ->withBody($payload)
            ->send();

        return $response->json()['FormOfPaymentResponse']['FormOfPayment']['Identifier']['value'] ?? null;

    }

    /**
     * Step 3 - Attach a payment offer that links cost elements (fare, taxes, etc.)
     */
    private function addPaymentOffer(string $reservationId, array $payload): array
    {
        $response = $this->client
            ->request('post', "/paymentoffer/reservationworkbench/{$reservationId}/payments")
            ->withBody($payload)
            ->send();

        return $response->json();
    }

    /**
     * Step 4 - Finalize the reservation to commit booking and payment
     */
    private function finalizeReservation(string $reservationId): array
    {
        $response = $this->client
            ->request('get', "/book/session/reservationworkbench/{$reservationId}")
            ->send();

        return $response->json();
    }

    /**
     * Step 5 - Retrieve the finalized reservation from Travelport
     */
    private function getFinalReservation(string $reservationId): ?array
    {
        $response = $this->client
            ->request('post', "/book/reservation/reservations/{$reservationId}")
            ->withBody([])
            ->send();

        return $response->json();
    }

    private function getReviewReservationSummary(string $PNR): ?array
    {
        $response = $this->client
            ->request('get', "/book/reservation/reservations/{$PNR}")
            ->withBody([])
            ->send();

        return $response->json();
    }



    private function setReservationData(array $data, array $cache): void
    {
        $reservation = $data['ReservationResponse']['Reservation'];

        $this->reservationWorkbenchId = $data['ReservationResponse']['Identifier']['value'] ?? '';
        $this->totalPrice = $cache['total_price']; // Placeholder if not available in response
        $this->currencyCode = $cache['currency']; // Default or set dynamically if present

        $this->addFOPIdentifierValue = $reservation['Receipt'][0]['Identifier']['value'] ?? '';

        $this->reservationResponseReservationOfferIdentifierId = $reservation['Receipt'][0]['Identifier']['value'] ?? '';
        $this->reservationResponseReservationOfferIdentifierAuthority = $reservation['Receipt'][0]['Identifier']['authority'] ?? 'Travelport';
        $this->reservationResponseReservationOfferIdentifierValue = $reservation['Receipt'][0]['Identifier']['value'] ?? '';

    }
    private function buildPaymentPayload(): array
    {
        return [
            'Payment' => [
                'id' => 'payment_1',
                'Identifier' => [
                    'authority' => 'Travelport',
                    'value' => $this->addFOPIdentifierValue,
                ],
                'Amount' => [
                    'code' => $this->currencyCode,
                    'minorUnit' => 2,
                    'currencySource' => 'Charged',
                    'approximateInd' => true,
                    'value' => $this->totalPrice,
                ],
                'FormOfPaymentIdentifier' => [
                    'id' => 'formOfPayment_1',
                    'FormOfPaymentRef' => 'formOfPayment_1',
                    'Identifier' => [
                        'authority' => 'Travelport',
                        'value' => $this->addFOPIdentifierValue,
                    ],
                ],
                'OfferIdentifier' => [
                    [
                        'id' => $this->reservationResponseReservationOfferIdentifierId,
                        'offerRef' => $this->reservationResponseReservationOfferIdentifierId,
                        'Identifier' => [
                            'authority' => $this->reservationResponseReservationOfferIdentifierAuthority,
                            'value' => $this->reservationResponseReservationOfferIdentifierValue,
                        ],
                    ],
                ],
            ],
        ];
    }

}
