<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Redoy\FlyHub\DTOs\Shared\PassengerDTO;

class BookingRequestDTO
{
    public string $offerId;
    public string $searchId;
    public ?string $offerRef = null;
    public array $passengers = [];
    public string $contactEmail;
    public string $contactPhone;

    public function __construct(array $data)
    {

        if (empty($data['offer_id']) || empty($data['search_id'])) {
            throw new \InvalidArgumentException('Both offer_id and search_id are required.');
        }

        $this->offerId = $data['offer_id'];
        $this->searchId = $data['search_id'];

        if (empty($data['passengers']) || !is_array($data['passengers'])) {
            throw new \InvalidArgumentException('Passengers are required.');
        }

        foreach ($data['passengers'] as $passenger) {
            $this->passengers[] = new PassengerDTO($passenger);
        }

        if (empty($data['contact']['email']) || empty($data['contact']['phone'])) {
            throw new \InvalidArgumentException('Contact email and phone are required.');
        }

        $this->contactEmail = $data['contact']['email'];
        $this->contactPhone = $data['contact']['phone'];
    }

    /**
     * Get the search ID.
     */
    public function getSearchId(): string
    {
        return $this->searchId;
    }

    /**
     * Get the offer ID.
     */
    public function getOfferId(): string
    {
        return $this->offerId;
    }

    /**
     * Get the passengers.
     *
     * @return PassengerDTO[]
     */
    public function getPassengers(): array
    {
        return $this->passengers;
    }

    /**
     * Get the contact email.
     */
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    /**
     * Get the contact phone.
     */
    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }
}
