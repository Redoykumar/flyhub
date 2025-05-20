<?php

namespace Redoy\FlyHub\DTOs\Requests;


use Illuminate\Support\Facades\Validator;
use Redoy\FlyHub\Cache\OfferIdentifiersCache;

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
        $offerCache = new OfferIdentifiersCache();

        $validator = Validator::make($data, [
            'search_id' => [
                'required',
                'string',
                fn($attr, $val, $fail) =>
                $offerCache->hasSearch($val) ?: $fail("No offers found for the provided search ID. Please verify and try again.")
            ],
            'offer_id' => [
                'required',
                'string',
                function ($attr, $val, $fail) use ($data, $offerCache) {
                    if (empty($data['search_id']) || !$offerCache->hasOffer($data['search_id'], $val)) {
                        $fail("The offer ID '{$val}' is not valid for the given search ID. Please check and try again.");
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            throw new \Exception('Validation failed: ' . implode(' ', $validator->errors()->all()));
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