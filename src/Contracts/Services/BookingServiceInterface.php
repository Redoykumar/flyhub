<?php

namespace Redoy\FlyHub\Contracts\Services;

use Redoy\FlyHub\DTOs\Requests\BookingRequestDTO;
use Redoy\FlyHub\DTOs\Responses\BookingResponseDTO;



interface BookingServiceInterface
{
    /**
     * Create a booking based on the provided request data.
     *
     * @param BookingRequestDTO $request The booking request data.
     * @return BookingResponseDTO The booking response data.
     * @throws \FlyHub\Exceptions\BookingFailedException If the booking process fails.
     */
    public function book(BookingRequestDTO $request, array $data): BookingResponseDTO;
}