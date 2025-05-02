<?php

namespace Redoy\FlyHub\DTOs\Responses;

class SearchResponseDTO
{
    public $status;
    public $data;
    public $meta;
    public $errors;

    // Constructor sets response data
    public function __construct(array $data,$meta=null)
    {
        $this->status = 'success';
        $this->data = $data;
        $this->meta = [];
        $this->errors = [];
    }

    // Convert to array for JSON response
    public function toArray()
    {
        return [
            'status' => $this->status,
            'data' => $this->data,
            'meta' => $this->meta,
            'errors' => $this->errors
        ];
    }
}