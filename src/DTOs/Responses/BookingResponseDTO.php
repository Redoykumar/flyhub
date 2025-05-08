<?php

namespace FlyHub\DTOs\Responses;

class BookingResponseDTO
{
    /**
     * The unique identifier of the booking.
     *
     * @var string|null
     */
    public $bookingId;

    /**
     * The status of the booking (e.g., confirmed, pending, failed).
     *
     * @var string
     */
    public $status;

    /**
     * The total price of the booking.
     *
     * @var float
     */
    public $totalPrice;

    /**
     * The currency of the total price (e.g., USD, EUR).
     *
     * @var string
     */
    public $currency;

    /**
     * BookingResponseDTO constructor.
     *
     * @param array $data Associative array of booking response data.
     * @throws \InvalidArgumentException If required fields are missing or invalid.
     */
    public function __construct(array $data)
    {
        // Set bookingId (optional, may be null for failed bookings)
        $this->bookingId = $data['bookingId'] ?? null;

        // Validate and set status
        if (empty($data['status']) || !is_string($data['status'])) {
            throw new \InvalidArgumentException('Booking status is required and must be a string.');
        }
        $this->status = $data['status'];

        // Validate and set totalPrice
        if (!isset($data['totalPrice']) || !is_numeric($data['totalPrice']) || $data['totalPrice'] < 0) {
            throw new \InvalidArgumentException('Total price is required and must be a non-negative number.');
        }
        $this->totalPrice = (float) $data['totalPrice'];

        // Validate and set currency
        if (empty($data['currency']) || !is_string($data['currency'])) {
            throw new \InvalidArgumentException('Currency is required and must be a string.');
        }
        $this->currency = $data['currency'];
    }
}