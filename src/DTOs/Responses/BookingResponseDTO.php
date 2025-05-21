<?php
namespace Redoy\FlyHub\DTOs\Responses;

class BookingResponseDTO
{
    public ?string $id;
    public ?string $pnr;
    public string $status;

    public array $travelers;
    public array $sequences;
    public array $price;
    public array $confirmation;
    public string $provider;
    private $cache = null;

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
        $this->cache = $data['storeCache'] ?? 'unknown';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPnr(): ?string
    {
        return $this->pnr;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTravelers(): array
    {
        return $this->travelers;
    }

    public function getSequences(): array
    {
        return $this->sequences;
    }

    public function getPrice(): array
    {
        return $this->price;
    }

    public function getConfirmation(): array
    {
        return $this->confirmation;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setCache($value): void
    {
        $this->cache = $value;
    }

    public function getCache()
    {
        return $this->cache;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pnr' => $this->pnr,
            'status' => $this->status,
            'travelers' => $this->travelers,
            'sequences' => $this->sequences,
            'price' => $this->price,
            'confirmation' => $this->confirmation,
            'provider' => $this->provider
        ];
    }


}