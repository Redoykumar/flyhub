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
                'trip_type' => $this->request->trip_type ?? 'Unknown',
                'sequences' => $this->extractSequence($combination, $offerId),
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
            'base' => $priceData['base'] ?? 0.0,
            'total_taxes' => $priceData['TotalTaxes'] ?? 0.0,
            'total_fees' => array_reduce($priceData['fees'], fn($carry, $fee) => $carry + (float) $fee['amount'], 0.0) ?? 0.0,
            'total_price' => $priceData['grandTotal'] ?? 0.0,
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
        dd($combination);
        $sequences = [];
        foreach ($combination['itineraries'] as $key => $offer) {
            // $this->offerIdentifiers[$this->searchIdentifier][$offerId][] = [
            //     'provider' => 'travelport',
            //     'CatalogProductOfferingsIdentifier' => $this->catalogOfferingsId,
            //     'CatalogProductOfferingIdentifier' => $offer['id'] ?? '',
            //     'ProductIdentifier' => $offer['productRef'] ?? '',
            // ];
            $sequences[$key] = [
                'brand' => [
                    'name' => $combination['travelerPricings'][0]['fareDetailsBySegment'][0]['brandedFareLabel'] ?? 'Unknown',
                    'imageURL' => null,
                ],
                'terms_and_conditions' => $this->getTermsAndConditions($offer['TermsAndConditions']['termsAndConditionsRef'] ?? null),
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
    private function getTermsAndConditions(?string $termsRef): ?array
    {
        if ($termsRef && isset($this->termsMap[$termsRef])) {
            $termsDetails = [];
            return [
                'baggage_allowance' => $this->extractBaggageAllowance($termsDetails['BaggageAllowance'] ?? []),
                'payment_timeLimit' => $termsDetails['PaymentTimeLimit'] ?? null,
                'penalties' => $this->extractPenalties($termsDetails['Penalties'] ?? []),
            ];
        }
        return null;
    }

    /**
     * Extracts baggage allowance details.
     *
     * @param array $baggageAllowance Baggage allowance data
     * @return array Formatted baggage allowance
     */
    private function extractBaggageAllowance(array $baggageAllowance): array
    {
        return array_map(function ($allowance) {
            return [
                'baggage_type' => $allowance['baggageType'] ?? null, // Example: "Checked", "Cabin"
                'airline_code' => $allowance['airlineCode'] ?? null, // Example: "AA" for American Airlines
                'url' => $allowance['url'] ?? null, // Example: link to baggage policy URL
            ];
        }, $baggageAllowance);
    }


    /**
     * Extracts penalty details.
     *
     * @param array $penalties Penalty data
     * @return array Formatted penalties
     */
    private function extractPenalties(array $penalties): array
    {
        return array_map(function ($penalty) {
            // Extract Change Penalty
            $changePenalty = isset($penalty['change']) ? [
                'applies_to' => $penalty['change']['appliesTo'] ?? null,
                'penalty_type' => $penalty['change']['penaltyType'] ?? null,
                'amount' => $penalty['change']['amount'] ?? 0.0,
            ] : null;

            // Extract Cancel Penalty
            $cancelPenalty = isset($penalty['cancel']) ? [
                'applies_to' => $penalty['cancel']['appliesTo'] ?? null,
                'penalty_type' => $penalty['cancel']['penaltyType'] ?? null,
                'amount' => $penalty['cancel']['amount'] ?? 0.0,
                'currency' => $penalty['cancel']['currency'] ?? null,
            ] : null;

            // Return both change and cancel penalties
            return [
                'change_penalty' => $changePenalty,
                'cancel_penalty' => $cancelPenalty,
            ];
        }, $penalties);
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
