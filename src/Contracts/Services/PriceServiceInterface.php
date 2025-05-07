<?php

namespace Redoy\FlyHub\Contracts\Services;

use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;

interface PriceServiceInterface
{
    public function price(array $request): PriceResponseDTO;
}