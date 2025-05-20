<?php

namespace Redoy\FlyHub\Providers\Travelport\Transformers;


use Symfony\Component\Uid\Ulid;
use Redoy\FlyHub\Helpers\SimplifyNumber;
use Redoy\FlyHub\Cache\OfferIdentifiersCache;

/**
 * Transforms Travelport API response data into a structured format for flight offers.
 */
class SearchTransformer
{
    private array $responseData;
    private $queryDetails;
    private string $catalogOfferingsId;
    private array $flightsMap = [];
    private array $productsMap = [];
    private array $termsMap = [];
    private array $brandsMap = [];
    private array $catalogOfferings = [];
    private array $combinationsBySequence = [];
    private array $combinationsByCode = [];
    private array $offerIdentifiers = [];
    private OfferIdentifiersCache $offerIdentifiersCache;

    /**
     * Initializes the transformer with API response, request data, and cache.
     *
     * @param array $responseData API response data
     * @param mixed $request Query request details
     * @param OfferIdentifiersCache $offerIdentifiersCache Cache handler for offer identifiers
     */
    public function __construct(array $responseData, $request)
    {
        $this->responseData = $responseData;
        $this->queryDetails = $request;
        $this->offerIdentifiersCache = new OfferIdentifiersCache();
        $this->catalogOfferingsId = $responseData['CatalogProductOfferingsResponse']['CatalogProductOfferings']['Identifier']['value'] ?? '';

        $referenceList = $responseData['CatalogProductOfferingsResponse']['ReferenceList'] ?? [];
        $referenceByType = array_column($referenceList, null, '@type');

        $this->flightsMap = $referenceByType['ReferenceListFlight']['Flight'] ?? [];
        $this->flightsMap = array_column($this->flightsMap, null, 'id');

        $this->productsMap = $referenceByType['ReferenceListProduct']['Product'] ?? [];
        $this->productsMap = array_column($this->productsMap, null, 'id');

        $this->termsMap = $referenceByType['ReferenceListTermsAndConditions']['TermsAndConditions'] ?? [];
        $this->termsMap = array_column($this->termsMap, null, 'id');

        $this->brandsMap = $referenceByType['ReferenceListBrand']['Brand'] ?? [];
        $this->brandsMap = array_column($this->brandsMap, null, 'id');

        $this->catalogOfferings = $responseData['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'] ?? [];
    }

    /**
     * Transforms the raw data into structured flight offer data.
     *
     * @return array Structured response with flight offers
     */
    public function transform(): array
    {
        $offerCombinations = [];
        $parsedOfferings = $this->parseOfferingsByCombinability();

        foreach ($parsedOfferings['combinationsByCode'] as $comboSet) {
            $offerCombinations = array_merge($offerCombinations, $this->generateCartesianProduct($comboSet));
        }

        $result = $this->createResponse($offerCombinations);
        $this->storeCacheOfferIdentifiers(); // Cache after search is complete
        return $result;
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
            'provider' => 'travelport',
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
        $flightOffers = [];
        foreach ($combinations as $index => $combination) {
            $offerId = $this->generateUniqueId();
            $flightOffers[$index] = [
                'id' => $offerId,
                'price' => $this->extractPrice($combination[0]['BestCombinablePrice'] ?? []),
                'passengers' => $this->extractPassengerDetails($combination[0]['BestCombinablePrice']['PriceBreakdown'] ?? []),
                'trip_type' => $this->queryDetails->getTripType() ?? 'Unknown',
                'sequences' => $this->extractSequence($combination, $offerId),
                'provider' => 'travelport'
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
        return 'offer:' . Ulid::generate();
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
            'currency' => $priceData['CurrencyCode']['value'] ?? 'USD',
            'base' => number_format((float) ($priceData['Base'] ?? 0.0), 2, '.', ''),
            'total_taxes' => number_format((float) ($priceData['TotalTaxes'] ?? 0.0), 2, '.', ''),
            'total_fees' => number_format((float) ($priceData['TotalFees'] ?? 0.0), 2, '.', ''),
            'total_price' => number_format((float) ($priceData['TotalPrice'] ?? 0.0), 2, '.', ''),
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
            switch ($item['requestedPassengerType'] ?? '') {
                case 'ADT':
                    $passengerDetails['adults'] += (int) ($item['quantity'] ?? 0);
                    break;
                case 'CNN':
                    $passengerDetails['children'] += (int) ($item['quantity'] ?? 0);
                    break;
                case 'INF':
                    $passengerDetails['infants'] += (int) ($item['quantity'] ?? 0);
                    break;
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
        $sequences = [];
        foreach ($combination as $key => $offer) {
            $this->offerIdentifiers[$offerId]['offerRef']['CatalogProductOfferingsIdentifier'] = $this->catalogOfferingsId;
            $this->offerIdentifiers[$offerId]['offerRef']['products'][] = [
                // 'CatalogProductOfferingsIdentifier' => $this->catalogOfferingsId,
                'CatalogProductOfferingIdentifier' => $offer['id'] ?? '',
                'ProductIdentifier' => $offer['productRef'] ?? '',
            ];
            $this->offerIdentifiers[$offerId]['provider'] = 'travelport';

            $sequences[$key] = [
                'brand' => isset($offer['Brand']) ? $this->getBrand($offer['Brand']['BrandRef']) : ['name' => 'Unknown'],
                'terms_and_conditions' => $this->getTermsAndConditions($offer['TermsAndConditions']['termsAndConditionsRef'] ?? null),
                'sequence' => $offer['sequence'] ?? 0,
                'departure' => $offer['departure'] ?? [],
                'arrival' => $offer['arrival'] ?? [],
                'total_duration' => SimplifyNumber::convertDurationToMinutes($offer['product']['totalDuration'] ?? '0'),
                'service_class' => $this->extractPassengerFlights($offer['product']['PassengerFlight'] ?? []),
                'flight_segments' => $this->getFlightSegments($offer['product']['FlightSegment'] ?? []),
                'stops' => count($offer['product']['FlightSegment'] ?? [])-1,
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
    private function extractPassengerFlights(array $passengerFlights): array
    {
        return array_map(function ($item) {
            $product = $item['FlightProduct'][0] ?? [];
            return [
                'type' => $item['passengerTypeCode'] ?? null,
                'qty' => $item['passengerQuantity'] ?? null,
                'class' => $product['classOfService'] ?? null,
                'cabin' => $product['cabin'] ?? null,
                'fare_code' => $product['fareBasisCode'] ?? null,
                'brand' => isset($product['Brand']) ? $this->getBrand($product['Brand']['BrandRef']) : ['name' => 'Unknown'],
            ];
        }, $passengerFlights);
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
            $termsDetails = $this->termsMap[$termsRef];
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
                'baggage_type' => $allowance['baggageType'] ?? null,
                'airline_code' => $allowance['validatingAirlineCode'] ?? null,
                'url' => $allowance['url'] ?? null,
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
            $change = $penalty['Change'][0] ?? null;
            $changePenalty = $change ? [
                'applies_to' => $change['PenaltyAppliesTo'] ?? null,
                'penalty_type' => $change['@type'] ?? null,
                'amount' => $change['Penalty'][0]['Amount']['value'] ?? 0.0,
            ] : null;

            $cancel = $penalty['Cancel'][0] ?? null;
            $cancelPenalty = $cancel ? [
                'applies_to' => $cancel['PenaltyAppliesTo'] ?? null,
                'penalty_type' => $cancel['@type'] ?? null,
                'amount' => $cancel['Penalty'][0]['Amount']['value'] ?? 0.0,
                'currency' => $cancel['Penalty'][0]['Amount']['code'] ?? null,
            ] : null;

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
            $flightDetails = $this->flightsMap[$segment['Flight']['FlightRef'] ?? ''] ?? null;
            if (isset($flightDetails)) {
                $flightSegments[$key] = [
                    'carrier' => $flightDetails['carrier'] ?? 'Unknown',
                    'airline_code' => $flightDetails['operatingCarrier'] ?? 'Unknown',
                    'airline_name' => $flightDetails['operatingCarrierName'] ?? 'Unknown',
                    'airline_imageUrl' => 'https://images.kiwi.com/airlines/64/' . ($flightDetails['operatingCarrier'] ?? 'Unknown') . '.png',
                    'flight_number' => $flightDetails['number'] ?? 'Unknown',
                    'equipment' => $flightDetails['equipment'] ?? 'Unknown',
                    'distance' => $flightDetails['distance'] ?? 0,
                    'duration' => SimplifyNumber::convertDurationToMinutes($flightDetails['duration'] ?? '0'),
                    'departure' => [
                        'location' => $flightDetails['Departure']['location'] ?? 'Unknown',
                        'date' => $flightDetails['Departure']['date'] ?? 'Unknown',
                        'time' => $flightDetails['Departure']['time'] ?? 'Unknown',
                    ],
                    'arrival' => [
                        'location' => $flightDetails['Arrival']['location'] ?? 'Unknown',
                        'date' => $flightDetails['Arrival']['date'] ?? 'Unknown',
                        'time' => $flightDetails['Arrival']['time'] ?? 'Unknown',
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

    /**
     * Parses offerings by combinability codes and sequences.
     *
     * @return array Parsed offerings data
     */
    private function parseOfferingsByCombinability(): array
    {
        $combinationsBySequence = [];
        $combinationsByCode = [];

        foreach ($this->catalogOfferings as $offering) {
            $offerId = $offering['id'] ?? '';
            $sequence = $offering['sequence'] ?? '';
            $departure = $offering['Departure'] ?? [];
            $arrival = $offering['Arrival'] ?? [];

            foreach ($offering['ProductBrandOptions'] ?? [] as $option) {
                foreach ($option['ProductBrandOffering'] ?? [] as $brandOffer) {
                    $brandOffer['id'] = $offerId;
                    $brandOffer['sequence'] = $sequence;
                    $brandOffer['departure'] = $departure;
                    $brandOffer['arrival'] = $arrival;
                    $brandOffer['productRef'] = $brandOffer['Product'][0]['productRef'] ?? null;
                    $brandOffer['product'] = $this->productsMap[$brandOffer['productRef']] ?? null;

                    $combinationsBySequence[$sequence][] = $brandOffer;

                    foreach ($brandOffer['CombinabilityCode'] ?? [] as $code) {
                        $combinationsByCode[$code][$sequence][] = $brandOffer;
                    }
                }
            }
        }

        $this->combinationsBySequence = $combinationsBySequence;
        $this->combinationsByCode = $combinationsByCode;

        return [
            'offeringsBySequence' => $combinationsBySequence,
            'combinationsByCode' => $combinationsByCode,
        ];
    }
    /**
     * Caches all offer identifiers for the current search.
     *
     * @return void
     */
    private function storeCacheOfferIdentifiers(): void
    {
        $this->offerIdentifiersCache->store(
            $this->queryDetails->getSearchId(),
            $this->offerIdentifiers ?? []
        );
    }
}