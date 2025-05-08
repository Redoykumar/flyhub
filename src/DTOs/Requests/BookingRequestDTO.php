<?php

namespace FlyHub\DTOs\Requests;

use FlyHub\DTOs\Shared\PassengerDTO;

class BookingRequestDTO
{
    /**
     * The unique identifier of the offer to book.
     *
     * @var string
     */
    public $offerId;

    /**
     * Array of passengers for the booking.
     *
     * @var PassengerDTO[]
     */
    public $passengers;

    /**
     * Contact email for the booking.
     *
     * @var string
     */
    public $contactEmail;

    /**
     * Contact phone number for the booking.
     *
     * @var string
     */
    public $contactPhone;

    /**
     * BookingRequestDTO constructor.
     *
     * @param array $data Associative array of booking request data.
     * @throws \InvalidArgumentException If required fields are missing or invalid.
     */
    public function __construct(array $data)
    {
        // Validate and set offerId
        if (empty($data['offerId']) || !is_string($data['offerId'])) {
            throw new \InvalidArgumentException('Offer ID is required and must be a string.');
        }
        $this->offerId = $data['offerId'];

        // Validate and set passengers
        if (empty($data['passengers']) || !is_array($data['passengers'])) {
            throw new \InvalidArgumentException('At least one passenger is required.');
        }
        // $this->passengers = array_map(function ($passenger) {
        //     return $passenger instanceof PassengerDTO ? $passenger : new PassengerDTO($passenger);
        // }, $data['passengers']);

        // Validate and set contactEmail
        if (empty($data['contactEmail']) || !filter_var($data['contactEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A valid contact email is required.');
        }
        $this->contactEmail = $data['contactEmail'];

        // Validate and set contactPhone
        if (empty($data['contactPhone']) || !is_string($data['contactPhone'])) {
            throw new \InvalidArgumentException('Contact phone number is required and must be a string.');
        }
        $this->contactPhone = $data['contactPhone'];
    }
}