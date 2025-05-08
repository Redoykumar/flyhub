<?php

namespace FlyHub\DTOs\Responses;

class PaymentResponseDTO
{
    /**
     * The unique identifier of the payment transaction.
     *
     * @var string|null
     */
    public $transactionId;

    /**
     * The status of the payment (e.g., success, failed, pending).
     *
     * @var string
     */
    public $status;

    /**
     * The paid amount.
     *
     * @var float
     */
    public $amount;

    /**
     * The currency of the paid amount (e.g., USD, EUR).
     *
     * @var string
     */
    public $currency;

    /**
     * PaymentResponseDTO constructor.
     *
     * @param array $data Associative array of payment response data.
     * @throws \InvalidArgumentException If required fields are missing or invalid.
     */
    public function __construct(array $data)
    {
        // Set transactionId (optional, may be null for failed payments)
        $this->transactionId = $data['transactionId'] ?? null;

        // Validate and set status
        if (empty($data['status']) || !is_string($data['status'])) {
            throw new \InvalidArgumentException('Payment status is required and must be a string.');
        }
        $this->status = $data['status'];

        // Validate and set amount
        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] < 0) {
            throw new \InvalidArgumentException('Amount is required and must be a non-negative number.');
        }
        $this->amount = (float) $data['amount'];

        // Validate and set currency
        if (empty($data['currency']) || !is_string($data['currency'])) {
            throw new \InvalidArgumentException('Currency is required and must be a string.');
        }
        $this->currency = $data['currency'];
    }
}