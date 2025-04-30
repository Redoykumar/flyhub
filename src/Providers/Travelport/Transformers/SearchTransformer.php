<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;


class SearchTransformer
{
    protected array $rawData;
    protected  $queryInfo;
    protected string $catalogProductOfferingsIdentifierValue;
    protected string $searchId;
    protected array $flightsById = [];
    protected array $productsById = [];
    protected array $termsById = [];
    protected array $brandById = [];
    protected array $offerings = [];
    protected array $combinabilityBySequence = [];
    protected array $combinabilityByCode = [];

    public function __construct(array $responseData, $request)
    {
        $searchId=uniqid('search_id_',);
        $this->rawData = $responseData;
        $this->queryInfo = $request;
        $this->catalogProductOfferingsIdentifierValue = $responseData['CatalogProductOfferingsResponse']['CatalogProductOfferings']['Identifier']['value'];

        $referenceList = $responseData['CatalogProductOfferingsResponse']['ReferenceList'] ?? [];
        $referenceByType = array_column($referenceList, null, '@type');

        $this->flightsById = isset($referenceByType['ReferenceListFlight']['Flight'])
            ? array_column($referenceByType['ReferenceListFlight']['Flight'], null, 'id')
            : [];

        $this->productsById = isset($referenceByType['ReferenceListProduct']['Product'])
            ? array_column($referenceByType['ReferenceListProduct']['Product'], null, 'id')
            : [];

        $this->termsById = isset($referenceByType['ReferenceListTermsAndConditions']['TermsAndConditions'])
            ? array_column($referenceByType['ReferenceListTermsAndConditions']['TermsAndConditions'], null, 'id')
            : [];
        $this->brandById = isset($referenceByType['ReferenceListBrand']['Brand'])
            ? array_column($referenceByType['ReferenceListBrand']['Brand'], null, 'id')
            : [];

        $this->offerings = $responseData['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'] ?? [];
      
    }

    public function transform(): array
    {
        $finalCombinations = [];
        $parsedOfferings = $this->parseOfferingsByCombinability();

        foreach ($parsedOfferings['combinationsByCode'] as $comboSet) {
            $finalCombinations = array_merge($finalCombinations, $this->generateCartesianProduct($comboSet));
        }
        dd($this->createResponse($finalCombinations));
        return $finalCombinations;
    }
    public function createResponse($finalCombinations)
    {
        return [
            'provider' => 'Travelport',  // Provider name
            'flights' => $this->generateFlightOffers($finalCombinations),  // The generated flight combinations
            'meta' => [
                'total' => count($finalCombinations),
                'status' => 'success',
            ]
        ];
    }

    protected function generateCartesianProduct(array $inputArrays): array
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

    protected function parseOfferingsByCombinability(): array
    {
        $combinationsBySequence = [];
        $combinationsByCode = [];

        foreach ($this->offerings as $offering) {
            $id = $offering['id'];
            $sequence = $offering['sequence'];
            $departure = $offering['Departure'];
            $arrival = $offering['Arrival'];
            foreach ($offering['ProductBrandOptions'] ?? [] as $option) {
                foreach ($option['ProductBrandOffering'] ?? [] as $brandOffer) {
                    $brandOffer['id'] = $id;
                    $brandOffer['sequence'] = $sequence;
                    $brandOffer['departure'] = $departure;
                    $brandOffer['arrival'] = $arrival;
                    $brandOffer['productRef'] = $brandOffer['Product'][0]['productRef'] ?? null;
                    $brandOffer['product'] = $this->productsById[$brandOffer['Product'][0]['productRef']] ?? null;

                    $combinationsBySequence[$sequence][] = $brandOffer;

                    foreach ($brandOffer['CombinabilityCode'] ?? [] as $code) {
                        $combinationsByCode[$code][$sequence][] = $brandOffer;
                    }
                }
            }
        }

        $this->combinabilityBySequence = $combinationsBySequence;
        $this->combinabilityByCode = $combinationsByCode;

        return [
            'offeringsBySequence' => $combinationsBySequence,
            'combinationsByCode' => $combinationsByCode,
        ];
    }


    public function generateFlightOffers(array $flightCombinations): array
    {
        $flightOffers = [];
        foreach ($flightCombinations as $index => $combination) {
            $flightOffers[$index] = [
                'id' => $this->generateUniqueId(),
                'price' => $this->extractPrice($combination),
                'passengers' => $this->extractPassengerDetails($combination),
                'trip_type' => $this->queryInfo->trip_type ?? 'Unknown',
                'sequences' => $this->extractSequence($combination),
            ];
        }

        return $flightOffers;
    }


    private function generateUniqueId(): string
    {
        // Generates a unique ID for each offer
        return uniqid('offer_', true);
    }

    private function extractPrice(array $flightCombination)
    {
        if ($flightCombination[0]['BestCombinablePrice'] !== null) {
            // Extract Base, Taxes, Fees, and Total Price if available
            return [
                'currency' => $flightCombination[0]['BestCombinablePrice']['CurrencyCode']['value'],
                'base' => $flightCombination[0]['BestCombinablePrice']['Base'] ?? 0,
                'total_taxes' => $flightCombination[0]['BestCombinablePrice']['TotalTaxes'] ?? 0,
                'total_fees' => $flightCombination[0]['BestCombinablePrice']['TotalFees'] ?? 0,
                'total_price' => $flightCombination[0]['BestCombinablePrice']['TotalPrice'] ?? 0,
            ];
        }

        return null;
    }

    private function extractPassengerDetails(array $flightCombination): array
    {
        $passengerDetails = [
            'adults' => 0,
            'children' => 0,
            'infants' => 0,
        ];

        foreach ($flightCombination[0]['BestCombinablePrice']['PriceBreakdown'] as $breakdown) {
            switch ($breakdown['requestedPassengerType']) {
                case 'ADT': // Adult
                    $passengerDetails['adults'] += $breakdown['quantity'];
                    break;
                case 'CNN': // Child
                    $passengerDetails['children'] += $breakdown['quantity'];
                    break;
                case 'INF': // Infant
                    $passengerDetails['infants'] += $breakdown['quantity'];
                    break;
            }
        }

        return $passengerDetails;
    }
    public function extractSequence(array $flightCombination): array
    {
        $sequences = [];
        foreach ($flightCombination as $key => $offer) {
            $sequences[$key] = [
                'brand' => isset($offer['Brand']) ? $this->getBrand($offer['Brand']['BrandRef']):"Unknown ",
                'terms_and_conditions' => $this->getTermsAndConditions($offer),
                'sequence' => $offer['sequence']??1,
                'departure' => $offer['departure'],
                'arrival' => $offer['arrival'],
                'total_duration'=>$this->convertDurationToMinutes($offer['product']['totalDuration']),
                'service_class'=>$this->extractPassengerFlights($offer['product']['PassengerFlight']),
                'flight_segments' => $this->getFlightSegments($offer['product']['FlightSegment']),
            ];
        }
        return $sequences;
    }
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
                'brand' => isset($product['Brand']) ? $this->getBrand($product['Brand']['BrandRef']) : "Unknown "
            ];
        }, $passengerFlights);
    }




    // Format Terms and Conditions
    public function getTermsAndConditions(array $offer)
    {
        // Check if the 'TermsAndConditions' and 'termsAndConditionsRef' exist
        $termsRef = $offer['TermsAndConditions']['termsAndConditionsRef'] ?? null;

        // If the reference exists, fetch the details from the termsById array
        if ($termsRef && isset($this->termsById[$termsRef])) {
            $termsDetails = $this->termsById[$termsRef];

            return [
                'baggage_allowance' => $this->extractBaggageAllowance($termsDetails['BaggageAllowance'] ?? []),
                'payment_timeLimit' => $termsDetails['PaymentTimeLimit'] ?? null,
                'penalties' => $this->extractPenalties($termsDetails['Penalties'] ?? []),
            ];
        }

        // Return null if no valid terms and conditions found
        return null;
    }

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

    private function extractPenalties(array $penalties): array
    {
        return array_map(function ($penalty) {
            // Extract Change Penalty
            $change = $penalty['Change'][0] ?? null;
            $changePenalty = $change ? [
                'applies_to' => $change['PenaltyAppliesTo'] ?? null,
                'penalty_type' => $change['@type'] ?? null,
                'amount' => $change['Penalty'][0]['Amount']['value'] ?? 0,
            ] : null;

            // Extract Cancel Penalty
            $cancel = $penalty['Cancel'][0] ?? null;
            $cancelPenalty = $cancel ? [
                'applies_to' => $cancel['PenaltyAppliesTo'] ?? null,
                'penalty_type' => $cancel['@type'] ?? null,
                'amount' => $cancel['Penalty'][0]['Amount']['value'] ?? 0,
                'currency' => $cancel['Penalty'][0]['Amount']['code'] ?? null,
            ] : null;

            return [
                'change_penalty' => $changePenalty,
                'cancel_penalty' => $cancelPenalty,
            ];
        }, $penalties);
    }



    function convertDurationToMinutes($duration)
    {
        // Match the duration in the format PTxHyM (x = hours, y = minutes)
        if (preg_match('/PT(\d+)H(\d+)M/', $duration, $matches)) {
            $hours = (int) $matches[1]; // Hours part
            $minutes = (int) $matches[2]; // Minutes part
        
            // Convert the total time to minutes
            return ($hours * 60) + $minutes;
        }
    
        // Return 0 if the format is not recognized
        return 0;
    }





    // Format Flight Segments
    public function getFlightSegments(array $offer)
    {
        $flightSegments = [];

        foreach ($offer as $key => $value) {
            // Get the flight details based on the FlightRef
            $flightDetails = $this->flightsById[$value['Flight']['FlightRef']] ?? null;

            if ($flightDetails) {
                // Format the flight segment as required
                $flightSegments[$key] = [
                    'carrier' => $flightDetails['carrier'] ?? 'Unknown',
                    'flight_number' => $flightDetails['number'] ?? 'Unknown',
                    'equipment' => $flightDetails['equipment'] ?? 'Unknown',
                    'distance' => $flightDetails['distance'] ?? 0,
                    'duration' => $this->convertDurationToMinutes($flightDetails['distance']) ?? 'Unknown',
                    'departure' => [
                        'location' => $flightDetails['Departure']['location'] ?? 'Unknown',
                        'date' => $flightDetails['Departure']['date'] ?? 'Unknown',
                        'time' => $flightDetails['Departure']['time'] ?? 'Unknown',
                    ],
                    'drrival' => [
                        'location' => $flightDetails['Arrival']['location'] ?? 'Unknown',
                        'date' => $flightDetails['Arrival']['date'] ?? 'Unknown',
                        'time' => $flightDetails['Arrival']['time'] ?? 'Unknown',
                    ],
                ];
            }
        }

        return $flightSegments;
    }


    // Format Brand information
    public function getBrand(string $brandRef): array
    {
        if (isset($this->brandById[$brandRef])) {
            $brandDetails = $this->brandById[$brandRef];

            return [
                'name' => $brandDetails['name'] ?? null,
                'imageURL' => $brandDetails['imageURL'] ?? null,
            ];
        }

        return [];
    }

}
