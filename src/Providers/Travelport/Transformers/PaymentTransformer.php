<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Redoy\FlyHub\DTOs\Requests\PaymentRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PaymentResponseDTO;

class PaymentTransformer
{
    private array $responseData;

    public function __construct(array $responseData, PaymentRequestDTO $request)
    {
        $this->responseData = $responseData;
    }

    public function transform(): PaymentResponseDTO
    {
        $reservation = $this->responseData['ReservationResponse']['Reservation'] ?? null;

        if (!$reservation) {
            throw new \RuntimeException('Invalid response: Missing Reservation data');
        }

        $paymentArray = $reservation['Payment'] ?? null;

        if (empty($paymentArray) || !is_array($paymentArray)) {
            throw new \RuntimeException('Invalid payment response: Missing Payment data');
        }

        // Get the first payment entry
        $payment = $paymentArray[0];

        // Build data array for DTO
        $data = [
            'id' => $payment['Identifier']['value'] ?? null,
            'pnr' => $this->extractPnr($reservation),
            'status' => $this->extractStatus($reservation),
            'travelers' => $this->extractTravelers($reservation),
            'sequences' => $this->extractSequences($reservation),
            'price' => $this->extractPrice($reservation),
            'confirmation' => $this->extractConfirmation($reservation),
            'provider' => 'travelport',
        ];

        return new PaymentResponseDTO($data);
    }
    private function extractPnr(array $reservation): ?string
    {
        $receipts = $reservation['Receipt'] ?? [];
        foreach ($receipts as $receipt) {
            if (isset($receipt['Confirmation']['Locator']['value'])) {
                return $receipt['Confirmation']['Locator']['value'];
            }
        }
        return null;
    }

    private function extractStatus(array $reservation): string
    {
        $receipts = $reservation['Receipt'] ?? [];
        foreach ($receipts as $receipt) {
            $statusList = $receipt['Confirmation']['OfferStatus']['StatusAir'] ?? [];
            foreach ($statusList as $status) {
                if (isset($status['value'])) {
                    return $status['value']; // e.g. "Confirmed"
                }
            }
        }
        return 'Unknown';
    }

    private function extractTravelers(array $reservation): array
    {
        $travelersRaw = $reservation['Traveler'] ?? [];
        $travelers = [];

        foreach ($travelersRaw as $traveler) {
            $travelers[] = [
                'id' => $traveler['id'] ?? null,
                'type' => $traveler['passengerTypeCode'] ?? null,
                'name' => [
                    'given' => $traveler['PersonName']['Given'] ?? null,
                    'surname' => $traveler['PersonName']['Surname'] ?? null,
                ],
                'birth_date' => $traveler['birthDate'] ?? null,
                'gender' => $traveler['gender'] ?? null,
                'contact' => [
                    'email' => $traveler['Email'][0]['value'] ?? null,
                    'phone' => [
                        'country_code' => $traveler['Telephone'][0]['countryAccessCode'] ?? null,
                        'number' => $traveler['Telephone'][0]['phoneNumber'] ?? null,
                    ],
                ],
                'documents' => $this->extractDocuments($traveler),
            ];
        }

        return $travelers;
    }

    private function extractDocuments(array $traveler): array
    {
        $documentsRaw = $traveler['TravelDocument'] ?? [];
        $documents = [];

        foreach ($documentsRaw as $doc) {
            $documents[] = [
                'doc_number' => $doc['docNumber'] ?? null,
                'doc_type' => $doc['docType'] ?? null,
                'expire_date' => $doc['expireDate'] ?? null,
                'issue_country' => $doc['issueCountry'] ?? null,
            ];
        }

        return $documents;
    }

    private function extractSequences(array $reservation): array
    {
        $sequences = [];
        $offers = $reservation['Offer'] ?? [];

        foreach ($offers as $offer) {
            $products = $offer['Product'] ?? [];
            foreach ($products as $product) {
                if (($product['@type'] ?? '') === 'ProductAir') {
                    $flightSegments = $product['FlightSegment'] ?? [];
                    $passengerFlights = $product['PassengerFlight'] ?? [];

                    $serviceClassBySegment = [];

                    foreach ($passengerFlights as $pFlight) {
                        foreach ($pFlight['FlightProduct'] as $flightProduct) {
                            foreach ($flightProduct['segmentSequence'] as $segmentSeq) {
                                $serviceClassBySegment[$segmentSeq] = [
                                    'type' => $pFlight['passengerTypeCode'] ?? null,
                                    'quantity' => $pFlight['passengerQuantity'] ?? 1,
                                    'class' => $flightProduct['classOfService'] ?? null,
                                    'cabin' => $flightProduct['cabin'] ?? null,
                                    'fare_code' => null, // no data in your JSON for fare_code
                                    'brand' => [
                                        'name' => 'Unknown',
                                        'image_url' => null,
                                    ],
                                ];
                            }
                        }
                    }

                    // Group flight segments into sequences by their sequence number
                    foreach ($flightSegments as $segment) {
                        $seqNum = $segment['sequence'] ?? null;
                        if ($seqNum === null) {
                            continue;
                        }

                        $flight = $segment['Flight'] ?? [];

                        $departure = $flight['Departure'] ?? [];
                        $arrival = $flight['Arrival'] ?? [];

                        $sequences[] = [
                            'sequence' => $seqNum,
                            'departure' => $departure['location'] ?? null,
                            'arrival' => $arrival['location'] ?? null,
                            'total_duration' => $this->parseDuration($flight['duration'] ?? null),
                            'stops' => 0, // Your data doesn't have stops info, defaulting to 0
                            'brand' => [
                                'name' => 'Unknown',
                                'image_url' => null,
                            ],
                            'service_class' => isset($serviceClassBySegment[$seqNum]) ? [$serviceClassBySegment[$seqNum]] : [],
                            'flight_segments' => [
                                [
                                    'carrier' => $flight['carrier'] ?? null,
                                    'flight_number' => $flight['number'] ?? null,
                                    'equipment' => $flight['equipment'] ?? null,
                                    'duration' => $this->parseDuration($flight['duration'] ?? null),
                                    'distance' => null,
                                    'airline_image_url' => "https://images.kiwi.com/airlines/64/{$flight['carrier']}.png",
                                    'operating_carrier' => null,
                                    'operating_carrier_name' => null,
                                    'departure' => [
                                        'location' => $departure['location'] ?? null,
                                        'date' => $departure['date'] ?? null,
                                        'time' => $departure['time'] ?? null,
                                        'terminal' => null,
                                    ],
                                    'arrival' => [
                                        'location' => $arrival['location'] ?? null,
                                        'date' => $arrival['date'] ?? null,
                                        'time' => $arrival['time'] ?? null,
                                        'terminal' => null,
                                    ],
                                    'stops' => [],
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        return $sequences;
    }

    private function parseDuration(?string $duration): ?int
    {
        // Input like "PT3H7M"
        if (!$duration) {
            return null;
        }

        $hours = 0;
        $minutes = 0;

        if (preg_match('/PT(\d+)H(\d+)M/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
        } elseif (preg_match('/PT(\d+)H/', $duration, $matches)) {
            $hours = (int) $matches[1];
        } elseif (preg_match('/PT(\d+)M/', $duration, $matches)) {
            $minutes = (int) $matches[1];
        }

        return $hours * 60 + $minutes;
    }

    private function extractPrice(array $reservation): array
    {
        $offers = $reservation['Offer'] ?? [];

        if (empty($offers)) {
            return [
                'currency' => null,
                'base' => null,
                'total_taxes' => null,
                'total_fees' => null,
                'total_price' => null,
                'price_per_passenger' => [],
            ];
        }

        $offer = $offers[0];
        $price = $offer['Price'] ?? [];

        return [
            'currency' => $price['CurrencyCode']['value'] ?? null,
            'base' => (string) ($price['Base'] ?? '0.00'),
            'total_taxes' => (string) ($price['TotalTaxes'] ?? '0.00'),
            'total_fees' => (string) ($price['TotalFees'] ?? '0.00'),
            'total_price' => (string) ($price['TotalPrice'] ?? '0.00'),
            'price_per_passenger' => [], // Not detailed in your data
        ];
    }

    private function extractConfirmation(array $reservation): array
    {
        $receipts = $reservation['Receipt'] ?? [];

        $confirmation = [
            'type' => 'ConfirmationHold',
            'creation_date' => null,
            'expires_at' => null,
        ];

        foreach ($receipts as $receipt) {
            if (isset($receipt['Confirmation']['Locator']['creationDate'])) {
                $confirmation['creation_date'] = $receipt['Confirmation']['Locator']['creationDate'];
            }
            if (isset($receipt['Confirmation']['ExpiryDate'])) {
                $confirmation['expires_at'] = $receipt['Confirmation']['ExpiryDate'];
            }
        }

        return $confirmation;
    }
}
