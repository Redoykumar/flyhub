<?php
namespace Redoy\FlyHub\Providers\Amadeus\Transformers;

use Ramsey\Uuid\Uuid;
use Redoy\FlyHub\Helpers\SimplifyNumber;

class SearchTransformer
{
    private $responseData;
    private $request;

    public function __construct(array $responseData, $request)
    {
        $this->responseData = $responseData;
        $this->request = $request;
    }

    public function transform()
    {

        // Return the structured response using the helper methods
        return $this->createResponse($this->responseData['data'] ?? []);
    }

    /**
     * Creates the final structured response.
     *
     * @param array $combinations Flight offer combinations
     * @return array Structured response
     */
    private function createResponse(array $combinations): array
    {
        return [
            'provider' => 'Amadeus',
            'flights' => $this->generateFlightOffers($combinations),
            'meta' => [
                'total' => count($combinations),
                'status' => 'success',
            ],
        ];
    }



    /**
     * Generates structured flight offers from combinations.
     *
     * @param array $combinations Flight combinations
     * @return array Structured flight offers
     */
    private function generateFlightOffers(array $combinations): array
    {
        // dd($this->responseData);
        $flightOffers = [];
        foreach ($combinations as $index => $combination) {
            $offerId = $this->generateUniqueId();
            // dd($combination);
            $flightOffers[$index] = [
                'id' => $offerId,
                'price' => $this->extractPrice($combination['price'] ?? []),
                'passengers' => $this->extractPassengerDetails($combination['travelerPricings'] ?? []),
                'trip_type' => $this->request->getTripType() ?? 'Unknown',
                'sequences' => $this->extractSequence($combination, $offerId),
                'provider' => 'amadeus'
            ];
        }
        return $flightOffers;
    }

    /**
     * Generates a unique identifier for offers.
     *
     * @return string Unique identifier
     */
    private function generateUniqueId(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Extracts price details from flight combination.
     *
     * @param array $priceData Price data
     * @return array|null Price details or null if invalid
     */
    private function extractPrice(array $priceData): ?array
    {
        if (empty($priceData)) {
            return null;
        }

        return [
            'currency' => $priceData['currency'] ?? 'USD',
            'base' => number_format((float) ($priceData['base'] ?? 0.0), 2, '.', ''),
            'total_taxes' => number_format((float) ($priceData['TotalTaxes'] ?? 0.0), 2, '.', ''),
            'total_fees' => number_format(
                array_reduce(
                    $priceData['fees'] ?? [],
                    fn($carry, $fee) => $carry + (float) ($fee['amount'] ?? 0),
                    0.0
                ),
                2,
                '.',
                ''
            ),
            'total_price' => number_format((float) ($priceData['grandTotal'] ?? 0.0), 2, '.', ''),
        ];
    }

    /**
     * Extracts passenger details from price breakdown.
     *
     * @param array $breakdown Price breakdown data
     * @return array Passenger details
     */
    private function extractPassengerDetails(array $breakdown): array
    {
        $passengerDetails = [
            'adults' => 0,
            'children' => 0,
            'infants' => 0,
        ];

        foreach ($breakdown as $item) {
            $type = strtoupper($item['travelerType'] ?? '');

            if (in_array($type, ['ADULT', 'SENIOR', 'STUDENT'])) {
                $passengerDetails['adults'] += 1;
            } elseif ($type === 'CHILD') {
                $passengerDetails['children'] += 1;
            } elseif (in_array($type, ['HELD_INFANT', 'SEATED_INFANT'])) {
                $passengerDetails['infants'] += 1;
            }
        }

        return $passengerDetails;
    }



    /**
     * Extracts sequence details for flight offers.
     *
     * @param array $combination Flight combination
     * @param string $offerId Offer identifier
     * @return array Sequence details
     */
    private function extractSequence(array $combination, string $offerId): array
    {
        // dd($combination);
        $sequences = [];
        foreach ($combination['itineraries'] as $key => $offer) {
            // $this->offerIdentifiers[$this->searchIdentifier][$offerId][] = [
            //     'provider' => 'travelport',
            //     'CatalogProductOfferingsIdentifier' => $this->catalogOfferingsId,
            //     'CatalogProductOfferingIdentifier' => $offer['id'] ?? '',
            //     'ProductIdentifier' => $offer['productRef'] ?? '',
            // ];
            // dd($offer);
            $sequences[$key] = [
                'brand' => [
                    'name' => $combination['travelerPricings'][0]['fareDetailsBySegment'][0]['brandedFareLabel'] ?? 'Unknown',
                    'imageURL' => null,
                ],
                'terms_and_conditions' => $this->getTermsAndConditions($combination['travelerPricings'][0]['fareDetailsBySegment'][0], $offer['segments'][0]['carrierCode'], $combination) ?? null,
                'sequence' => $key + 1 ?? 0,
                'departure' => $offer['segments'][0]['departure']['iataCode'] ?? [],
                'arrival' => end($offer['segments'])['arrival']['iataCode'] ?? [],
                'total_duration' => SimplifyNumber::convertDurationToMinutes($offer['duration'] ?? '0'),
                'service_class' => $this->extractPassengerFlights($combination['travelerPricings'] ?? []),
                'flight_segments' => $this->getFlightSegments($offer['segments'] ?? []),
                'stops' => count($offer['segments'] ?? []),
            ];
        }
        return $sequences;
    }

    /**
     * Extracts passenger flight details.
     *
     * @param array $passengerFlights Passenger flight data
     * @return array Formatted passenger flight details
     */
    private function extractPassengerFlights(array $travelerPricings): array
    {
        return array_map(function ($item) {
            $segment = $item['fareDetailsBySegment'][0] ?? [];

            return [
                'type' => $item['travelerType'] ?? null,
                'qty' => 1, // Each entry represents 1 traveler
                'class' => $segment['class'] ?? null,
                'cabin' => $segment['cabin'] ?? null,
                'fare_code' => $segment['fareBasis'] ?? null,
                'brand' => [
                    'name' => $segment['brandedFareLabel'] ?? $segment['brandedFare'] ?? 'Unknown',
                    'imageURL' => null,
                ],
            ];
        }, $travelerPricings);
    }


    /**
     * Formats terms and conditions data.
     *
     * @param string|null $termsRef Terms reference ID
     * @return array|null Terms details or null if not found
     */
    private function getTermsAndConditions(?array $terms, $airline_code, $combination): ?array
    {

        return [
            'baggage_allowance' => $this->extractBaggageAllowance($terms ?? [], $airline_code),
            'payment_timeLimit' => $combination['lastTicketingDateTime'] ?? $combination['lastTicketingDate'] ?? null,
            'penalties' => $this->extractPenalties($terms ?? []),
        ] ?? null;

    }

    /**
     * Extracts baggage allowance details.
     *
     * @param array $baggageAllowance Baggage allowance data
     * @return array Formatted baggage allowance
     */
    private function extractBaggageAllowance(array $baggageAllowance, $airline_code): array
    {
        $types = [];

        if (!empty($baggageAllowance['includedCheckedBags'])) {
            $types[] = 'Checked';
        }

        if (!empty($baggageAllowance['includedCabinBags'])) {
            $types[] = 'CarryOn';
        }

        return [
            'baggage_type' => implode(', ', $types) ?: null,
            'airline_code' => $airline_code ?? null,
            'url' => $baggageAllowance['url'] ?? null,
        ];
    }



    /**
     * Extracts penalty details.
     *
     * @param array $penalties Penalty data
     * @return array Formatted penalties
     */
    private function extractPenalties(?array $penalties = null): array
    {
        // Fallback default penalty if no data is provided
        return [
            [
                'change_penalty' => [
                    'applies_to' => 'FLIGHT',
                    'penalty_type' => 'ChangePermitted',
                    'amount' => 0.0,
                ],
                'cancel_penalty' => [
                    'applies_to' => 'FLIGHT',
                    'penalty_type' => 'CancelNotPermitted',
                    'amount' => 0.0,
                    'currency' => 'USD',
                ],
            ]
        ];
    }





    /**
     * Formats flight segment details.
     *
     * @param array $segments Flight segment data
     * @return array Formatted flight segments
     */
    private function getFlightSegments(array $segments): array
    {

        $flightSegments = [];
        foreach ($segments as $key => $segment) {
            if (isset($segment)) {
                $flightSegments[$key] = [
                    'carrier' => $segment['carrierCode'] ?? 'Unknown',
                    'airline_imageUrl' => 'https://images.kiwi.com/airlines/64/' . ($segment['carrierCode'] ?? 'Unknown') . '.png',
                    'flight_number' => $segment['number'] ?? 'Unknown',
                    'equipment' => $segment['aircraft']['code'] ?? 'Unknown',
                    'distance' => $segment['distance'] ?? 0,
                    'duration' => SimplifyNumber::convertDurationToMinutes($segment['duration'] ?? '0'),
                    'departure' => [
                        'location' => $segment['departure']['iataCode'] ?? 'Unknown',
                        'date' => SimplifyNumber::extractDate($segment['departure']['at']) ?? 'Unknown',
                        'time' => SimplifyNumber::extractTime($segment['departure']['at']) ?? 'Unknown',
                    ],
                    'arrival' => [
                        'location' => $segment['arrival']['iataCode'] ?? 'Unknown',
                        'date' => SimplifyNumber::extractDate($segment['arrival']['at']) ?? 'Unknown',
                        'time' => SimplifyNumber::extractTime($segment['arrival']['at']) ?? 'Unknown',
                    ],
                ];
            }
        }
        return $flightSegments;
    }

    /**
     * Formats brand information.
     *
     * @param string $brandRef Brand reference ID
     * @return array Brand details
     */
    private function getBrand(string $brandRef): array
    {
        $brandDetails = $this->brandsMap[$brandRef] ?? [];
        return [
            'name' => $brandDetails['name'] ?? 'Unknown',
            'imageURL' => $brandDetails['imageURL'] ?? null,
        ];
    }


    /**
     * Generates Cartesian product of input arrays.
     *
     * @param array $inputArrays Arrays to combine
     * @return array Cartesian product combinations
     */
    private function generateCartesianProduct(array $inputArrays): array
    {
        $result = [[]];
        foreach ($inputArrays as $array) {
            $tempResult = [];
            foreach ($result as $prefix) {
                foreach ($array as $item) {
                    $tempResult[] = [...$prefix, $item];
                }
            }
            $result = $tempResult;
        }
        return $result;
    }

}
