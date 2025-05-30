<?php 
namespace Redoy\FlyHub\DTOs\Responses;

class PriceResponseDTO
{
    public array $offers;

    public function __construct(array $offers)
    {
        $this->offers = $offers;
    }

    public function toArray(): array
    {
        return [
            'offers' => $this->offers,
        ];
    }

    public function getOfferIds(): array
    {
        return array_column($this->offers, 'id');
    }
}
