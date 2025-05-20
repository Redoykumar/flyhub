<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\Log;
use Redoy\FlyHub\Helpers\SimplifyNumber;
use Redoy\FlyHub\DTOs\Requests\BookingRequestDTO;
use Redoy\FlyHub\DTOs\Responses\BookingResponseDTO;

class BookingTransformer
{
    private array $responseData;
    private BookingRequestDTO $request;
    private array $cache;

    public function __construct(array $responseData, BookingRequestDTO $request, array $cache)
    {

        $this->responseData = $responseData;
        $this->request = $request;
        $this->cache = $cache;
    }

    public function transform(): BookingResponseDTO
    {
        $reservation = $this->responseData['ReservationResponse']['Reservation'] ?? [];

        if (empty($reservation)) {
            Log::error('BookingTransformer: Missing Reservation in response', [
                'response' => $this->responseData
            ]);
            throw new \RuntimeException('Invalid booking response: Missing Reservation');
        }
        $id = $this->extractId($reservation);
        $pnr = $this->extractLocatorCode($reservation);
        $price = $this->extractPrice($reservation['Offer'][0]);
        return new BookingResponseDTO([
            'id' => $id,
            'pnr' => $pnr,
            'status' => $this->extractStatus($reservation),
            'travelers' => $this->extractTravelers($reservation['Traveler'] ?? []),
            'sequences' => $this->extractSequences($reservation['Offer'][0] ?? [], $this->cache),
            'price' => $price,
            'confirmation' => $this->extractConfirmation(end($reservation['Receipt']) ?? []),
            'provider' => 'travelport',
            'storeCache' => $this->storeBookingCache([$id => ['provider' => 'travelport', 'pnr' => $pnr, 'price' => $price]]),
        ]);
    }

    private function extractId(array $reservation): ?string
    {
        return 'booking:' . Ulid::generate();
    }

    private function extractLocatorCode(array $reservation, int $version = 6): ?string
    {
        if (!isset($reservation['Receipt']) || !is_array($reservation['Receipt'])) {
            return null;
        }

        foreach ($reservation['Receipt'] as $receipt) {
            if (
                ($version === 6 && ($receipt['Confirmation']['Locator']['source'] ?? null) === '1G') ||
                ($version !== 6 && ($receipt['Identifier']['authority'] ?? null) === 'Travelport')
            ) {
                return $receipt['Confirmation']['Locator']['value'] ?? null;
            }
        }

        return null;
    }

    private function extractStatus(array $reservation): string
    {
        $status = $reservation['Status'] ?? null;
        $resultStatus = $this->responseData['ReservationResponse']['Result']['status'] ?? null;
        $confirmationType = $reservation['Receipt'][0]['Confirmation']['@type'] ?? null;
        $errors = $this->responseData['ReservationResponse']['Result']['errors'] ?? [];

        // Standardized status mapping
        $statusMap = [
            'Confirmed' => 'Confirmed',
            'Pending' => 'Pending',
            'Hold' => 'Hold',
            'Failed' => 'Failed',
            'Cancelled' => 'Cancelled',
            'ConfirmationHold' => 'Hold',
        ];

        // Check confirmation type first (e.g., ConfirmationHold)
        if ($confirmationType === 'ConfirmationHold') {
            return 'Hold';
        }

        // Check reservation status
        if ($status && isset($statusMap[$status])) {
            return $statusMap[$status];
        }

        // Check result status
        if ($resultStatus && isset($statusMap[$resultStatus])) {
            return $statusMap[$resultStatus];
        }

        // Check for errors indicating failure or cancellation
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $message = strtolower($error['message'] ?? '');
                if (stripos($message, 'cancel') !== false) {
                    return 'Cancelled';
                }
                if (stripos($message, 'fail') !== false || stripos($message, 'error') !== false) {
                    return 'Failed';
                }
            }
        }

        // Default to Unknown
        Log::warning('BookingTransformer: Unknown status', [
            'reservation_status' => $status,
            'result_status' => $resultStatus,
            'confirmation_type' => $confirmationType,
        ]);
        return 'Unknown';
    }

    private function extractTravelers(array $travelers): array
    {
        return array_map(function ($traveler) {
            $name = $traveler['PersonName'] ?? [];
            $telephone = $traveler['Telephone'][0] ?? [];
            $email = $traveler['Email'][0] ?? [];
            $document = $traveler['TravelDocument'][0] ?? [];

            return [
                'id' => $traveler['id'] ?? null,
                'type' => $traveler['passengerTypeCode'] ?? 'Unknown',
                'name' => [
                    'given' => $name['Given'] ?? 'Unknown',
                    'surname' => $name['Surname'] ?? 'Unknown',
                ],
                'birth_date' => $traveler['birthDate'] ?? null,
                'gender' => $traveler['gender'] ?? null,
                'contact' => [
                    'email' => $email['value'] ?? null,
                    'phone' => [
                        'country_code' => $telephone['countryAccessCode'] ?? null,
                        'number' => $telephone['phoneNumber'] ?? null,
                    ],
                ],
                'documents' => $document ? [
                    [
                        'type' => $document['docType'] ?? null,
                        'number' => $document['docNumber'] ?? null,
                        'expiry_date' => $document['expireDate'] ?? null,
                        'issue_country' => $document['issueCountry'] ?? null,
                        'nationality' => $document['birthCountry'] ?? null,
                    ]
                ] : [],
            ];
        }, $travelers);
    }

    private function extractSequences(array $offer, array $cache): array
    {
        $products = $offer['Product'] ?? [];
        $cachedSequences = $cache['sequences'] ?? [];

        return array_map(function ($product, $index) use ($cachedSequences) {
            $cached = $cachedSequences[$index] ?? [];
            $segments = $product['FlightSegment'] ?? [];
            $firstSegment = $segments[0]['Flight'] ?? [];
            $lastSegment = end($segments)['Flight'] ?? [];

            return [
                'sequence' => $index + 1,
                'departure' => $firstSegment['Departure']['location'] ?? $cached['departure'] ?? 'Unknown',
                'arrival' => $lastSegment['Arrival']['location'] ?? $cached['arrival'] ?? 'Unknown',
                'total_duration' => SimplifyNumber::convertDurationToMinutes($product['totalDuration'] ?? '0'),
                'stops' => count($segments),
                'brand' => [
                    'name' => $cached['brand']['name'] ?? 'Unknown',
                    'image_url' => $cached['brand']['imageURL'] ?? null,
                ],
                'service_class' => $this->extractServiceClass($product['PassengerFlight'] ?? [], $cached),
                'flight_segments' => $this->extractFlightSegments($segments, $cached),
            ];
        }, $products, array_keys($products));
    }

    private function extractServiceClass(array $passengerFlights, array $cached): array
    {
        $cachedServiceClass = $cached['service_class'] ?? [];
        return array_map(function ($flight, $index) use ($cachedServiceClass) {
            $product = $flight['FlightProduct'][0] ?? [];
            $cached = $cachedServiceClass[$index] ?? [];
            return [
                'type' => $flight['passengerTypeCode'] ?? $cached['type'] ?? 'Unknown',
                'quantity' => $flight['passengerQuantity'] ?? $cached['qty'] ?? 1,
                'class' => $product['classOfService'] ?? $cached['class'] ?? null,
                'cabin' => $product['cabin'] ?? $cached['cabin'] ?? null,
                'fare_code' => $product['fareBasisCode'] ?? $cached['fare_code'] ?? null,
                'brand' => [
                    'name' => $cached['brand']['name'] ?? 'Unknown',
                    'image_url' => $cached['brand']['imageURL'] ?? null,
                ],
            ];
        }, $passengerFlights, array_keys($passengerFlights));
    }

    private function extractFlightSegments(array $segments, array $cached): array
    {
        $cachedSegments = $cached['flight_segments'] ?? [];
        return array_map(function ($segment, $index) use ($cachedSegments) {
            $flight = $segment['Flight'] ?? [];
            $cached = $cachedSegments[$index] ?? [];
            return [
                'carrier' => $flight['carrier'] ?? $cached['carrier'] ?? 'Unknown',
                'flight_number' => $flight['number'] ?? $cached['flight_number'] ?? 'Unknown',
                'equipment' => $flight['equipment'] ?? $cached['equipment'] ?? null,
                'duration' => SimplifyNumber::convertDurationToMinutes($flight['duration'] ?? ($cached['duration'] ?? '0')),
                'distance' => $flight['distance'] ?? $cached['distance'] ?? null,
                'airline_image_url' => SimplifyNumber::airlineImageUrl($flight['carrier'] ?? ($cached['carrier'] ?? null)),
                'operating_carrier' => $flight['operatingCarrier'] ?? $cached['operating_carrier'] ?? null,
                'operating_carrier_name' => $flight['operatingCarrierName'] ?? $cached['operating_carrier_name'] ?? null,
                'departure' => [
                    'location' => $flight['Departure']['location'] ?? $cached['departure']['location'] ?? 'Unknown',
                    'date' => $flight['Departure']['date'] ?? $cached['departure']['date'] ?? 'Unknown',
                    'time' => $flight['Departure']['time'] ?? $cached['departure']['time'] ?? 'Unknown',
                    'terminal' => $flight['Departure']['terminal'] ?? $cached['departure']['terminal'] ?? null,
                ],
                'arrival' => [
                    'location' => $flight['Arrival']['location'] ?? $cached['arrival']['location'] ?? 'Unknown',
                    'date' => $flight['Arrival']['date'] ?? $cached['arrival']['date'] ?? 'Unknown',
                    'time' => $flight['Arrival']['time'] ?? $cached['arrival']['time'] ?? 'Unknown',
                    'terminal' => $flight['Arrival']['terminal'] ?? $cached['arrival']['terminal'] ?? null,
                ],
                'stops' => $this->extractIntermediateStops($flight['IntermediateStop'] ?? []),
            ];
        }, $segments, array_keys($segments));
    }

    private function extractIntermediateStops(array $stops): array
    {
        return array_map(function ($stop) {
            return [
                'location' => $stop['value'] ?? 'Unknown',
                'arrival_date' => $stop['arrivalDate'] ?? null,
                'arrival_time' => $stop['arrivalTime'] ?? null,
                'departure_date' => $stop['departureDate'] ?? null,
                'departure_time' => $stop['departurelTime'] ?? null,
            ];
        }, $stops);
    }

    private function extractPrice(array $cache): array
    {
        $price = $cache['Price'] ?? null;

        if (!$price) {
            return [
                'currency' => 'USD',
                'base' => '0.00',
                'total_taxes' => '0.00',
                'total_fees' => '0.00',
                'total_price' => '0.00',
                'price_per_passenger' => [],
            ];
        }

        $currency = $price['CurrencyCode'][0] ?? 'USD';
        $base = number_format((float) ($price['Base'] ?? 0), 2, '.', '');
        $taxes = number_format((float) ($price['TotalTaxes'] ?? 0), 2, '.', '');
        $fees = number_format((float) ($price['TotalFees'] ?? 0), 2, '.', '');
        $total = number_format((float) ($price['TotalPrice'] ?? 0), 2, '.', '');

        $pricePerPassenger = [];

        foreach ($price['PriceBreakdown'] ?? [] as $item) {
            $type = $item['requestedPassengerType'] ?? 'ADT';
            $quantity = $item['quantity'] ?? 1;
            $filedAmount = $item['FiledAmount'] ?? [];

            $passengerPrice = [
                'type' => $type,
                'quantity' => $quantity,
                'base' => number_format((float) ($filedAmount['value'] ?? 0), 2, '.', ''),
                'taxes' => $taxes, // assuming uniform tax across all
                'fees' => $fees,
                'total' => $total,
                'tax_breakdown' => [], // Not available in provided data
            ];

            $pricePerPassenger[] = $passengerPrice;
        }

        return [
            'currency' => $currency,
            'base' => $base,
            'total_taxes' => $taxes,
            'total_fees' => $fees,
            'total_price' => $total,
            'price_per_passenger' => $pricePerPassenger,
        ];
    }


    private function extractConfirmation(array $receipt): array
    {

        $confirmation = $receipt['Confirmation'] ?? [];
        $locator = $confirmation['Locator'] ?? [];
        $confirmationType = $confirmation['@type'] ?? 'ConfirmationHold';
        $creationDate = $locator['creationDate'] ?? null;

        $expiresAt = null;
        if ($confirmationType === 'ConfirmationHold') {
            try {
                $baseTime = $creationDate ? Carbon::parse($creationDate) : Carbon::now('Asia/Dhaka');
                $expiresAt = $baseTime->addMinutes(30)->toIso8601String();
            } catch (\Exception $e) {
                Log::warning('BookingTransformer: Failed to calculate expires_at', [
                    'creation_date' => $creationDate,
                    'error' => $e->getMessage(),
                ]);
                $expiresAt = Carbon::now('Asia/Dhaka')->addMinutes(30)->toIso8601String();
            }
        }

        return [
            'type' => $confirmationType,
            'creation_date' => $creationDate,
            'expires_at' => $expiresAt,
        ];
    }
    private function storeBookingCache(array $data): array
    {
        return $data;

    }
}