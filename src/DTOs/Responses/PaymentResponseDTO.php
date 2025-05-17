<?php

namespace Redoy\FlyHub\DTOs\Responses;

class PaymentResponseDTO
{
    public $id;
    public $pnr;
    public $status;
    public $travelers;
    public $sequences;
    public $price;
    public $confirmation;
    public $provider;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;

        $this->pnr = $data['pnr'] ?? null;

        $this->status = $data['status'] ?? null;
        if (!is_string($this->status) && !is_null($this->status)) {
            throw new \InvalidArgumentException('Status must be a string or null.');
        }

        $this->travelers = $data['travelers'] ?? [];
        if (!is_array($this->travelers)) {
            throw new \InvalidArgumentException('Travelers must be an array.');
        }

        $this->sequences = $data['sequences'] ?? [];
        if (!is_array($this->sequences)) {
            throw new \InvalidArgumentException('Sequences must be an array.');
        }

        $this->price = $data['price'] ?? [];
        if (!is_array($this->price)) {
            throw new \InvalidArgumentException('Price must be an array.');
        }

        $this->confirmation = $data['confirmation'] ?? [];
        if (!is_array($this->confirmation)) {
            throw new \InvalidArgumentException('Confirmation must be an array.');
        }

        $this->provider = $data['provider'] ?? null;
        if (!is_string($this->provider) && !is_null($this->provider)) {
            throw new \InvalidArgumentException('Provider must be a string or null.');
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPnr(): ?string
    {
        return $this->pnr;
    }

    public function getStatus(): ?string
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

    public function getProvider(): ?string
    {
        return $this->provider;
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
            'provider' => $this->provider,
        ];
    }
}
