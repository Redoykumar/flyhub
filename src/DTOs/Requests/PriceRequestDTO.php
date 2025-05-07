<?php

namespace Redoy\FlyHub\DTOs\Requests;

use Illuminate\Support\Facades\Validator;

class PriceRequestDTO
{
    public string $searchId;
    public string $offerId;

    /**
     * Initialize the DTO with search_id and offer_id.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->validate($data);

        $this->searchId = $data['search_id'];
        $this->offerId = $data['offer_id'];
    }

    /**
     * Validate the input data using Laravel Validator.
     *
     * @param array $data
     */
    private function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'search_id' => ['required', 'string'],
            'offer_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }
    }

    /**
     * Get the search ID.
     *
     * @return string
     */
    public function getSearchId(): string
    {
        return $this->searchId;
    }

    /**
     * Get the offer ID.
     *
     * @return string
     */
    public function getOfferId(): string
    {
        return $this->offerId;
    }
}