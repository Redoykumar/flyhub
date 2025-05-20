<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Illuminate\Support\Facades\Validator;
use Redoy\FlyHub\Cache\PriceCache;
use Redoy\FlyHub\DTOs\Shared\PassengerDTO;

class BookingRequestDTO
{
    public string $priceId;
    public ?string $offerRef = null;
    public array $passengers = [];
    public string $contactEmail;
    public string $contactPhone;

    public function __construct(array $data)
    {
        $priceCache = new PriceCache();

        $validator = Validator::make($data, [
            'price_id' => [
                'required',
                'string',
                fn($attr, $val, $fail) =>
                $priceCache->has($val)
                ?: $fail("Invalid or expired price ID. Please verify and try again."),
            ],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.first_name' => 'required|string',
            'passengers.*.last_name' => 'required|string',
            'passengers.*.gender' => 'required|in:M,F',
            'passengers.*.dob' => 'required|date',
            'passengers.*.passport_number' => 'required|string',
            'passengers.*.passport_expiry' => 'required|date',
            'passengers.*.nationality' => 'required|string|size:2',
            'passengers.*.passport_issued_country' => 'required|string|size:2',
            'passengers.*.type' => 'required|in:ADT,CHD,INF',
            'contact.email' => 'required|email',
            'contact.phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(' ', $validator->errors()->all()));
        }

        $this->priceId = $data['price_id'];

        foreach ($data['passengers'] as $passenger) {
            $this->passengers[] = new PassengerDTO($passenger);
        }

        $this->contactEmail = $data['contact']['email'];
        $this->contactPhone = $data['contact']['phone'];
    }

    public function getPriceId(): string
    {
        return $this->priceId;
    }

    /**
     * @return PassengerDTO[]
     */
    public function getPassengers(): array
    {
        return $this->passengers;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }
}
