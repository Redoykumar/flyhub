<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Ramsey\Uuid\Uuid;
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

        return new BookingResponseDTO([
            'id' => $this->extractId($reservation),
            'pnr' => $this->extractPnr($reservation),
            'status' => $this->extractStatus($reservation),
            'travelers' => $this->extractTravelers($reservation['Traveler'] ?? []),
            'sequences' => $this->extractSequences($reservation['Offer'][0] ?? [], $this->cache),
            'price' => $this->extractPrice($this->cache),
            'confirmation' => $this->extractConfirmation($reservation['Receipt'][0] ?? []),
            'provider' => 'travelport',
        ]);
    }

    private function extractId(array $reservation): ?string
    {
        return $reservation['Identifier']['value'] ?? null;
    }

    private function extractPnr(array $reservation): ?string
    {
        return $reservation['Receipt'][0]['Confirmation']['Locator']['value'] ?? null;
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
            'ConfirmationHold' => 'Hold', // From Receipt['Confirmation']['@type']
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
        \Log::warning('BookingTransformer: Unknown status', [
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
        $price = $cache['price'] ?? [
            'currency' => 'USD',
            'base' => '0.00',
            'total_taxes' => '0.00',
            'total_fees' => '0.00',
            'total_price' => '0.00',
            'price_per_passenger' => [],
        ];

        $pricePerPassenger = [];
        foreach ($price['price_per_passenger'] ?? [] as $type => $data) {
            $pricePerPassenger[] = [
                'type' => $type,
                'quantity' => $data['quantity'] ?? 1,
                'base' => $data['base'] ?? '0.00',
                'taxes' => $data['taxes'] ?? '0.00',
                'fees' => $data['fees'] ?? '0.00',
                'total' => $data['total'] ?? '0.00',
                'tax_breakdown' => array_map(function ($code, $tax) {
                    return [
                        'code' => $code,
                        'description' => $tax['description'] ?? 'Unknown',
                        'amount' => $tax['amount'] ?? '0.00',
                    ];
                }, array_keys($data['tax_breakdown'] ?? []), $data['tax_breakdown'] ?? []),
            ];
        }

        return [
            'currency' => $price['currency'] ?? 'USD',
            'base' => $price['base'] ?? '0.00',
            'total_taxes' => $price['total_taxes'] ?? '0.00',
            'total_fees' => $price['total_fees'] ?? '0.00',
            'total_price' => $price['total_price'] ?? '0.00',
            'price_per_passenger' => $pricePerPassenger,
        ];
    }

    private function extractConfirmation(array $receipt): array
    {
        $locator = $confirmation['Locator'] ?? [];

        return [
            'creation_date' => $locator['creationDate'] ?? null,
        ];
    }
}