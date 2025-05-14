<?php

namespace Redoy\FlyHub\DTOs\Requests;

class PaymentRequestDTO
{
    /**
     * The unique identifier of the booking to pay for.
     *
     * @var string
     */
    public $bookingId;

    /**
     * The payment method (e.g., credit_card, paypal).
     *
     * @var string
     */
    public $paymentMethod;

    /**
     * The payment amount.
     *
     * @var float
     */
    public $amount;

    /**
     * The currency of the payment (e.g., USD, EUR).
     *
     * @var string
     */
    public $currency;

    /**
     * Payment details (e.g., card number, expiry date).
     *
     * @var array
     */
    public $paymentDetails;

    /**
     * PaymentRequestDTO constructor.
     *
     * @param array $data Associative array of payment request data.
     * @throws \InvalidArgumentException If required fields are missing or invalid.
     */
    public function __construct(array $data)
    {
        // Validate and set bookingId
        if (empty($data['bookingId']) || !is_string($data['bookingId'])) {
            throw new \InvalidArgumentException('Booking ID is required and must be a string.');
        }
        $this->bookingId = $data['bookingId'];

        // Validate and set paymentMethod
        if (empty($data['paymentMethod']) || !is_string($data['paymentMethod'])) {
            throw new \InvalidArgumentException('Payment method is required and must be a string.');
        }
        $this->paymentMethod = $data['paymentMethod'];

        // Validate and set amount
        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \InvalidArgumentException('Amount is required and must be a positive number.');
        }
        $this->amount = (float) $data['amount'];

        // Validate and set currency
        if (empty($data['currency']) || !is_string($data['currency'])) {
            throw new \InvalidArgumentException('Currency is required and must be a string.');
        }
        $this->currency = $data['currency'];

        // Validate and set paymentDetails
        if (empty($data['paymentDetails']) || !is_array($data['paymentDetails'])) {
            throw new \InvalidArgumentException('Payment details are required and must be an array.');
        }
        $this->paymentDetails = $data['paymentDetails'];
    }
}