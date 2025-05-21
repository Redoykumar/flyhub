<?php

namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\DTOs\Shared\PassengerDTO;
use Redoy\FlyHub\DTOs\Requests\BookingRequestDTO;
use Redoy\FlyHub\DTOs\Responses\BookingResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Contracts\Services\BookingServiceInterface;
use Redoy\FlyHub\Providers\Travelport\Transformers\BookingTransformer;

class BookingService implements BookingServiceInterface
{
    protected TravelportClient $client;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;
    }

    public function book(BookingRequestDTO $request, array $cache): BookingResponseDTO
    {
        // $reservationId = $this->createReservationWorkbench();
        // $this->addCatalogOffer($reservationId, $cache);
        // $this->addTravelers($reservationId, $request);
        // $finalResponse = $this->finalizeReservation($reservationId);

        $arrayVar = [
            "ReservationResponse" => [
                "@type" => "ReservationResponse",
                "Reservation" => [
                    "@type" => "Reservation",
                    "Offer" => [
                        [
                            "@type" => "Offer",
                            "id" => "offer_1",
                            "Identifier" => [
                                "authority" => "Travelport",
                                "value" => "699253ad-431d-464b-8e1d-d75504e19f48",
                            ],
                            "Product" => [
                                [
                                    "@type" => "ProductAir",
                                    "id" => "product_1",
                                    "FlightSegment" => [
                                        [
                                            "@type" => "FlightSegment",
                                            "id" => "FlightSegment_01",
                                            "sequence" => 1,
                                            "connectionDuration" => "PT13H38M",
                                            "boundFlightsInd" => true,
                                            "Flight" => [
                                                "@type" => "FlightDetail",
                                                "duration" => "PT3H7M",
                                                "carrier" => "UA",
                                                "number" => "2408",
                                                "equipment" => "777",
                                                "id" => "Flight_01",
                                                "Departure" => [
                                                    "@type" => "Departure",
                                                    "location" => "DEN",
                                                    "date" => "2024-01-14",
                                                    "time" => "17:50:00",
                                                ],
                                                "Arrival" => [
                                                    "@type" => "Arrival",
                                                    "location" => "IAD",
                                                    "date" => "2024-01-14",
                                                    "time" => "22:57:00",
                                                ],
                                                "AvailabilitySourceCode" => "S",
                                            ],
                                        ],
                                        [
                                            "@type" => "FlightSegment",
                                            "id" => "FlightSegment_02",
                                            "sequence" => 2,
                                            "Flight" => [
                                                "@type" => "FlightDetail",
                                                "duration" => "PT2H3M",
                                                "carrier" => "UA",
                                                "number" => "1940",
                                                "equipment" => "319",
                                                "id" => "Flight_02",
                                                "Departure" => [
                                                    "@type" => "Departure",
                                                    "location" => "IAD",
                                                    "date" => "2024-01-15",
                                                    "time" => "12:35:00",
                                                ],
                                                "Arrival" => [
                                                    "@type" => "Arrival",
                                                    "location" => "ATL",
                                                    "date" => "2024-01-15",
                                                    "time" => "14:38:00",
                                                ],
                                                "AvailabilitySourceCode" => "S",
                                            ],
                                        ],
                                    ],
                                    "PassengerFlight" => [
                                        [
                                            "@type" => "PassengerFlight",
                                            "passengerQuantity" => 1,
                                            "passengerTypeCode" => "ADT",
                                            "FlightProduct" => [
                                                [
                                                    "@type" => "FlightProduct",
                                                    "segmentSequence" => [1],
                                                    "classOfService" => "K",
                                                    "cabin" => "Economy",
                                                ],
                                                [
                                                    "@type" => "FlightProduct",
                                                    "segmentSequence" => [2],
                                                    "classOfService" => "L",
                                                    "cabin" => "Economy",
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            "Price" => [
                                "@type" => "PriceDetail",
                                "id" => "PriceDetail_1",
                                "CurrencyCode" => ["value" => "AUD"],
                                "Base" => 387,
                                "TotalTaxes" => 76.3,
                                "TotalFees" => 0,
                                "TotalPrice" => 463.3,
                                "PriceBreakdown" => [
                                    [
                                        "@type" => "PriceBreakdownAir",
                                        "quantity" => 1,
                                        "requestedPassengerType" => "ADT",
                                        "Amount" => [
                                            "@type" => "Amount",
                                            "currencySource" => "Charged",
                                            "approximateInd" => true,
                                            "CurrencyCode" => [
                                                "decimalPlace" => 2,
                                                "value" => "AUD",
                                            ],
                                            "Base" => 387,
                                            "Taxes" => [
                                                "@type" => "TaxesDetail",
                                                "TotalTaxes" => 76.3,
                                                "Tax" => [
                                                    [
                                                        "currencyCode" => "AUD",
                                                        "taxCode" => "AY",
                                                        "value" => 17.8,
                                                    ],
                                                    [
                                                        "currencyCode" => "AUD",
                                                        "taxCode" => "US",
                                                        "value" => 29.1,
                                                    ],
                                                    [
                                                        "currencyCode" => "AUD",
                                                        "taxCode" => "XF",
                                                        "value" => 14.2,
                                                    ],
                                                    [
                                                        "currencyCode" => "AUD",
                                                        "taxCode" => "ZP",
                                                        "value" => 15.2,
                                                    ],
                                                ],
                                            ],
                                            "Total" => 463.3,
                                        ],
                                        "FiledAmount" => [
                                            "currencyCode" => "USD",
                                            "decimalPlace" => 2,
                                            "value" => 244.65,
                                        ],
                                    ],
                                ],
                            ],
                            "TermsAndConditionsFull" => [
                                [
                                    "@type" => "TermsAndConditionsFullAir",
                                    "Identifier" => [
                                        "authority" => "Travelport",
                                        "value" =>
                                            "0d528ac3-7752-4101-9761-291eaad3926b",
                                    ],
                                ],
                                [
                                    "@type" => "TermsAndConditionsFullAir",
                                    "ExpiryDate" => "2023-12-16T23:59:00Z",
                                    "PaymentTimeLimit" => "2023-12-16T23:59:00Z",
                                ],
                            ],
                        ],
                    ],
                    "Traveler" => [
                        [
                            "@type" => "Traveler",
                            "birthDate" => "1986-11-11",
                            "gender" => "Male",
                            "passengerTypeCode" => "ADT",
                            "id" => "travelerRefId_1",
                            "Identifier" => [
                                "authority" => "Travelport",
                                "value" => "c94f69d9-984d-4e25-87bb-d0e658f78594",
                            ],
                            "PersonName" => [
                                "@type" => "PersonName",
                                "Given" => "TESTFIRST",
                                "Surname" => "TESTLAST",
                            ],
                            "Telephone" => [
                                [
                                    "@type" => "TelephoneDetail",
                                    "countryAccessCode" => "1",
                                    "phoneNumber" => "212456121",
                                    "id" => "telephone_1",
                                    "cityCode" => "ORD",
                                    "role" => "Home",
                                ],
                            ],
                            "Email" => [["value" => "TravelerOne@gmail.com"]],
                            "TravelDocument" => [
                                [
                                    "@type" => "TravelDocumentDetail",
                                    "docNumber" => "A123123",
                                    "docType" => "Passport",
                                    "expireDate" => "2033-12-14",
                                    "issueCountry" => "US",
                                    "birthDate" => "1986-11-11",
                                    "Gender" => "Male",
                                    "PersonName" => [
                                        "@type" => "PersonName",
                                        "Given" => "TESTFIRST",
                                        "Surname" => "TESTLAST",
                                    ],
                                ],
                            ],
                        ],
                    ],
                    "Receipt" => [
                        [
                            "@type" => "ReceiptConfirmation",
                            "Identifier" => [
                                "authority" => "Travelport",
                                "value" => "a2bfd1db-dbab-44de-91cd-728c95af269f",
                            ],
                            "Confirmation" => [
                                "@type" => "ConfirmationHold",
                                "Locator" => [
                                    "source" => "1G",
                                    "creationDate" => "2023-12-14",
                                    "value" => "61K2S2",
                                ],
                                "OfferStatus" => [
                                    "@type" => "OfferStatusAir",
                                    "StatusAir" => [
                                        [
                                            "flightRefs" => ["Flight_01", "Flight_02"],
                                            "code" => "HK",
                                            "value" => "Confirmed",
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "@type" => "ReceiptConfirmation",
                            "Identifier" => [
                                "authority" => "Travelport",
                                "value" => "3e55767e-ef78-4bab-afef-6598ac40c8e5",
                            ],
                            "Confirmation" => [
                                "@type" => "ConfirmationHold",
                                "Locator" => ["source" => "UA", "value" => "E9ZHFW"],
                            ],
                        ],
                    ],
                    "ReservationDisplaySequence" => [
                        "@type" => "ReservationDisplaySequence",
                        "DisplaySequence" => [
                            [
                                "@type" => "DisplaySequence",
                                "displaySequence" => 1,
                                "OfferRef" => "offer_1",
                                "ProductRef" => "product_1",
                                "Sequence" => 1,
                            ],
                            [
                                "@type" => "DisplaySequence",
                                "displaySequence" => 2,
                                "OfferRef" => "offer_1",
                                "ProductRef" => "product_1",
                                "Sequence" => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $transformer = new BookingTransformer($arrayVar, $request, $cache);
        // $transformer = new BookingTransformer($finalResponse->json(), $request, $cache);
        return $transformer->transform();
    }

    private function createReservationWorkbench(): string
    {
        $body = [
            'ReservationID' => new \stdClass() // empty object
        ];

        $response = $this->client
            ->request('post', '/book/session/reservationworkbench')
            ->withBody($body)
            ->send();

        return $response->json()['ReservationResponse']['Reservation']['Identifier']['value'];
    }

    private function addCatalogOffer(string $reservationId, array $request): bool
    {
        $body = $this->buildBody($request);

        $response = $this->client
            ->request('post', "/book/airoffer/reservationworkbench/{$reservationId}/offers/buildfromcatalogproductofferings")
            ->withBody($body)
            ->send();

        return $response->successful();
    }

    private function addTravelers(string $reservationId, BookingRequestDTO $request): void
    {
        foreach ($request->passengers as $index => $passenger) {
            $travelerPayload = [
                'Traveler' => $this->buildTraveler($index, $passenger, $request),
            ];

            $response = $this->client
                ->request('post', "/book/traveler/reservationworkbench/{$reservationId}/travelers")
                ->withBody($travelerPayload)
                ->send();

        }
    }

    private function buildTraveler(int $index, PassengerDTO $passenger, BookingRequestDTO $request): array
    {
        return [
            'id' => 'trav_' . uniqid('', true),
            'gender' => $passenger->gender === 'M' ? 'Male' : 'Female',
            'birthDate' => $passenger->dob,
            'passengerTypeCode' => $passenger->type,
            'PersonName' => $this->buildPersonName($passenger),
            'Telephone' => [$this->buildTelephone($index, $request)],
            'Email' => [$this->buildEmail($request)],
            'TravelDocument' => [$this->buildTravelDocument($passenger)],
        ];
    }

    private function buildPersonName(PassengerDTO $passenger): array
    {
        return [
            '@type' => 'PersonNameDetail',
            'Given' => $passenger->firstName,
            'Surname' => $passenger->lastName,
        ];
    }

    private function buildTelephone(int $index, BookingRequestDTO $request): array
    {
        return [
            '@type' => 'Telephone',
            'countryAccessCode' => '880',
            'phoneNumber' => $request->contactPhone ?? '0000000000',
            'id' => (string) ($index + 1),
            'cityCode' => 'DAC',
            'role' => 'Home',
        ];
    }

    private function buildEmail(BookingRequestDTO $request): array
    {
        return [
            'value' => $request->contactEmail ?? 'default@example.com',
        ];
    }

    private function buildTravelDocument(PassengerDTO $passenger): array
    {
        return [
            '@type' => 'TravelDocument',
            'docNumber' => $passenger->passportNumber,
            'docType' => 'Passport',
            'expireDate' => $passenger->passportExpiry,
            'issueCountry' => $passenger->passportIssuedCountry,
            'birthDate' => $passenger->dob,
            'birthCountry' => $passenger->nationality,
            'Gender' => $passenger->gender === 'M' ? 'Male' : 'Female',
            'PersonName' => [
                '@type' => 'PersonName',
                'Given' => $passenger->firstName,
                'Surname' => $passenger->lastName,
            ],
        ];
    }

    private function finalizeReservation(string $reservationId)
    {
        return $this->client
            ->request('post', "/book/reservation/reservations/{$reservationId}")
            ->withBody([])
            ->send();
    }

    protected function buildBody(array $request): array
    {
        return [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequest' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'validateInventoryInd' => true,
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => [
                            'value' => $request['CatalogProductOfferingsIdentifier'],
                        ],
                    ],
                    'CatalogProductOfferingSelection' => array_map(function ($product) {
                        return [
                            'CatalogProductOfferingIdentifier' => [
                                'Identifier' => [
                                    'value' => $product['CatalogProductOfferingIdentifier'],
                                ],
                            ],
                            'ProductIdentifier' => [
                                [
                                    'Identifier' => [
                                        'value' => $product['ProductIdentifier'],
                                    ],
                                ],
                            ],
                        ];
                    }, $request['products']),
                ],
            ],
        ];
    }
}
