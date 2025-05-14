<?php
namespace Redoy\FlyHub\DTOs\Responses;

class BookingResponseDTO
{
    public ?string $id;
    public ?string $pnr;
    public string $status;
    public array $passengers;
    public array $travelers;
    public array $sequences;
    public array $price;
    public array $confirmation;
    public string $provider;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->pnr = $data['pnr'] ?? null;
        $this->status = $data['status'] ?? 'Unknown';
        $this->travelers = $data['travelers'] ?? [];
        $this->sequences = $data['sequences'] ?? [];
        $this->price = $data['price'] ?? [
            'currency' => 'USD',
            'base' => '0.00',
            'total_taxes' => '0.00',
            'total_fees' => '0.00',
            'total_price' => '0.00',
            'price_per_passenger' => [],
        ];
        $this->confirmation = $data['confirmation'] ?? [];
        $this->provider = $data['provider'] ?? 'unknown';
    }
}