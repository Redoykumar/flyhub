<?php

namespace FlyHub\Providers\Travelport\Services;

use FlyHub\Contracts\Services\BookingServiceInterface;
use FlyHub\DTOs\Requests\BookingRequestDTO;
use FlyHub\DTOs\Responses\BookingResponseDTO;
use FlyHub\Exceptions\BookingFailedException;
use FlyHub\Providers\Travelport\TravelportClient;
use FlyHub\Providers\Travelport\Transformers\BookingTransformer;

class BookingService implements BookingServiceInterface
{
    protected $client;
    protected $transformer;

    public function __construct(TravelportClient $client, BookingTransformer $transformer)
    {
        $this->client = $client;
        $this->transformer = $transformer;
    }

    public function book(BookingRequestDTO $request): BookingResponseDTO
    {
        try {
            // Prepare request data for Travelport API
            $apiRequestData = $this->prepareBookingRequest($request);

            // Call Travelport API
            $rawResponse = $this->client->createBooking($apiRequestData);

            // Transform the response into a standardized DTO
            return $this->transformer->transform($rawResponse);
        } catch (\Exception $e) {
            throw new BookingFailedException("Failed to create booking with Travelport: " . $e->getMessage());
        }
    }

    protected function prepareBookingRequest(BookingRequestDTO $request): array
    {
        // Map BookingRequestDTO to Travelport API format
        return [
            'AirBooking' => [
                'OfferId' => $request->offerId,
                'Passengers' => array_map(function ($passenger) {
                    return [
                        'FirstName' => $passenger->firstName,
                        'LastName' => $passenger->lastName,
                        'BirthDate' => $passenger->dateOfBirth,
                        'PassportNumber' => $passenger->passportNumber ?? null,
                    ];
                }, $request->passengers),
                'Contact' => [
                    'Email' => $request->contactEmail,
                    'Phone' => $request->contactPhone,
                ],
            ],
        ];
    }
}