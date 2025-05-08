<?php

namespace FlyHub\Contracts\Services;

use FlyHub\DTOs\Requests\BookingRequestDTO;
use FlyHub\DTOs\Responses\BookingResponseDTO;

interface BookingServiceInterface
{
    /**
     * Create a booking based on the provided request data.
     *
     * @param BookingRequestDTO $request The booking request data.
     * @return BookingResponseDTO The booking response data.
     * @throws \FlyHub\Exceptions\BookingFailedException If the booking process fails.
     */
    public function book(BookingRequestDTO $request): BookingResponseDTO;
}