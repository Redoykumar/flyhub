<?php
namespace Redoy\Flyhub\DTOs\Requests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentRequestDTO
{
    public const VALIDATION_RULES = [
        'booking_id' => ['required', 'string', 'max:255'],
        'payment_method' => ['required', 'string', 'in:card,token'],
        'payment_descriptions' => ['nullable', 'string', 'max:500'],
        'pnr' => ['nullable', 'string', 'max:50'],
    ];

    public const VALIDATION_MESSAGES = [
        'booking_id.required' => 'A booking ID is required for payment.',
        'booking_id.max' => 'The booking ID must not exceed 255 characters.',
        'payment_method.required' => 'A payment method is required.',
        'payment_method.in' => 'The payment method must be either "card" or "token".',
        'payment_descriptions.max' => 'The payment description must not exceed 500 characters.',
        'pnr.max' => 'The PNR must not exceed 50 characters.',
    ];

    protected string $booking_id;
    protected string $payment_method;
    protected ?string $payment_descriptions;
    protected ?string $pnr;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->validate($data);

        // Assign with safe fallbacks
        $this->booking_id = isset($data['booking_id']) && is_string($data['booking_id'])
            ? $data['booking_id']
            : '';
        $this->payment_method = isset($data['payment_method']) && in_array($data['payment_method'], ['card', 'token'])
            ? $data['payment_method']
            : 'card';
        $this->payment_descriptions = isset($data['payment_descriptions']) && is_string($data['payment_descriptions'])
            ? $data['payment_descriptions']
            : null;
        $this->pnr = isset($data['pnr']) && is_string($data['pnr'])
            ? $data['pnr']
            : null;
    }

    /**
     * Validate input data.
     *
     * @param array<string, mixed> $data
     */
    protected function validate(array $data): void
    {
        $validator = Validator::make($data, self::VALIDATION_RULES, self::VALIDATION_MESSAGES);

        if ($validator->fails()) {
            Log::warning('PaymentRequestDTO validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $data,
            ]);
        }
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'booking_id' => $this->booking_id,
            'payment_method' => $this->payment_method,
            'payment_descriptions' => $this->payment_descriptions,
            'pnr' => $this->pnr,
        ];
    }

    public function getBookingId(): string
    {
        return $this->booking_id;
    }

    public function getPaymentMethod(): string
    {
        return $this->payment_method;
    }

    public function getPaymentDescriptions(): ?string
    {
        return $this->payment_descriptions;
    }

    public function getPnr(): ?string
    {
        return $this->pnr;
    }
}
