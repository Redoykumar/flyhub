<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Illuminate\Http\Request as HttpRequest;

class PriceRequestDTO
{
    public string $offerId;
    /** @var string[] */
    public array $flightIds;
    public ?string $currency;
    public ?int $passengerCount;

    public function __construct(
        string $offerId,
        array $flightIds = [],
        ?string $currency = 'USD',
        ?int $passengerCount = 1
    ) {
        $this->offerId = $offerId;
        $this->flightIds = $flightIds;
        $this->currency = $currency;
        $this->passengerCount = $passengerCount;
    }

    public static function fromInput($input): self
    {
        if ($input instanceof self) {
            return $input;
        }

        $data = $input instanceof HttpRequest ? $input->all() : (is_array($input) ? $input : []);

        return new self(
            $data['offerId'] ?? '',
            $data['flightIds'] ?? $data['flight_ids'] ?? [],
            $data['currency'] ?? 'USD',
            $data['passengerCount'] ?? $data['passenger_count'] ?? 1
        );
    }
}