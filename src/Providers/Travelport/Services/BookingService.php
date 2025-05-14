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
        $reservationId = $this->createReservationWorkbench();
        $this->addCatalogOffer($reservationId, $cache);
        $this->addTravelers($reservationId, $request);
        $finalResponse = $this->finalizeReservation($reservationId);

        $transformer = new BookingTransformer($finalResponse->json(), $request, $cache);
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
