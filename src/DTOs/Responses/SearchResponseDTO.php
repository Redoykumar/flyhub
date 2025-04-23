<?php

namespace Redoy\FlyHub\DTOs\Responses;

class SearchResponseDTO
{
    public $status;
    public $data;
    public $errors;

    // Constructor sets response data
    public function __construct(array $data)
    {
        $this->status = 'success';
        $this->data = $data;
        $this->errors = [];
    }

    // Convert to array for JSON response
    public function toArray()
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'errors' => $this->errors
        ];
    }
}