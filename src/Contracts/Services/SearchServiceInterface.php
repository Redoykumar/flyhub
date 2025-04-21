<?php

namespace Redoy\FlyHub\Contracts\Services;

use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;

interface SearchServiceInterface
{
    public function search(SearchRequestDTO $request): SearchResponseDTO;
}