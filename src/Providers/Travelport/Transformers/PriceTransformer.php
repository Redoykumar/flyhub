<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Symfony\Component\Uid\Ulid;
use Redoy\FlyHub\Helpers\SimplifyNumber;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;

class PriceTransformer
{
    private array $responseData;
    private $queryDetails;
    private array $brandsMap = [];
    /**
     * Transform the raw Travelport API response into a PriceResponseDTO.
     *
     * @param array $response
     * @return PriceResponseDTO
     */
    public function __construct(array $responseData, $request)
    {
        $this->responseData = $responseData;
        $this->queryDetails = $request;

        $referenceList = $responseData['OfferListResponse']['ReferenceList'] ?? [];
        $referenceByType = array_column($referenceList, null, '@type');

        $this->brandsMap = $referenceByType['ReferenceListBrand']['Brand'] ?? [];
        $this->brandsMap = array_column($this->brandsMap, null, 'id');
    }
    public function transform(): PriceResponseDTO
    {

        return new PriceResponseDTO($this->generateFlightOffers($this->responseData['OfferListResponse']['OfferID']));
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
                'price' => $this->extractPrice($combination['Price'] ?? []),              
                'sequences' => $this->extractSequence($combination['Product']),
                'terms_and_conditions' => $this->extractTermsAndConditions($combination['TermsAndConditionsFull']),
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
        return 'price:' . Ulid::generate();
    }

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
            'price_per_passenger' => $this->pricePerPassenger($priceData['PriceBreakdown']),
        ];
    }
    private function pricePerPassenger(array $priceData): array
    {
        $result = [];

        foreach ($priceData as $item) {
            $type = $item['requestedPassengerType'] ?? 'UNK';
            $quantity = (int) ($item['quantity'] ?? 1);
            $amount = $item['Amount'] ?? [];

            $base = (float) ($amount['Base'] ?? 0.0);
            $taxes = (float) ($amount['Taxes']['TotalTaxes'] ?? 0.0);
            $fees = (float) ($amount['Fees']['TotalFees'] ?? 0.0);
            $total = (float) ($amount['Total'] ?? ($base + $taxes + $fees));

            $result[$type] = [
                'quantity' => $quantity,
                'base' => number_format($base, 2, '.', ''),
                'taxes' => number_format($taxes, 2, '.', ''),
                'fees' => number_format($fees, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'tax_breakdown' => $this->extractTaxBreakdown($amount['Taxes']['Tax'] ?? []),
            ];
        }

        return $result;
    }
    private function extractTaxBreakdown(array $taxes): array
    {
        $breakdown = [];

        foreach ($taxes as $tax) {
            $code = $tax['taxCode'] ?? 'UNK';
            $description = $tax['description'] ?? 'No description available';
            $amount = (float) ($tax['value'] ?? 0.0);

            if (!isset($breakdown[$code])) {
                $breakdown[$code] = [
                    'description' => $description,
                    'amount' => 0.0
                ];
            }

            $breakdown[$code]['amount'] += $amount;
        }

        // Format each tax amount and keep the description
        foreach ($breakdown as $code => $data) {
            $breakdown[$code]['amount'] = number_format($data['amount'], 2, '.', '');
        }

        return $breakdown;
    }

    public function extractTermsAndConditions(array $termsFull): array
    {
        return array_map(function ($term) {
            return [
                'secureFlightPassengerDataRequired' => $term['secureFlightPassengerDataRequiredInd'] ?? false,
                'expiryDate' => $term['ExpiryDate'] ?? null,
                'paymentTimeLimit' => $term['PaymentTimeLimit'] ?? null,
                'validatingAirline' => $this->extractValidatingAirline($term),
                'baggageAllowance' => $this->extractBaggageAllowances($term['BaggageAllowance'] ?? []),
                'penalties' => $this->extractPenalties($term['Penalties'] ?? []),
            ];
        }, $termsFull);
    }

    protected function extractValidatingAirline(array $term): ?string
    {
        return $term['ValidatingAirline'][0]['ValidatingAirline'] ?? null;
    }

    protected function extractBaggageAllowances(array $baggageList): array
    {
        return array_map(function ($baggage) {
            $item = $baggage['BaggageItem'][0] ?? [];
            $measurements = $item['Measurement'] ?? [];

            $weightKg = null;
            $weightLb = null;
            $dimensionCm = null;
            $dimensionIn = null;

            foreach ($measurements as $m) {
                if ($m['measurementType'] === 'Weight') {
                    if ($m['unit'] === 'Kilograms')
                        $weightKg = $m['value'];
                    if ($m['unit'] === 'Pounds')
                        $weightLb = $m['value'];
                }
                if ($m['measurementType'] === 'OverallDimension') {
                    if ($m['unit'] === 'Centimeters')
                        $dimensionCm = $m['value'];
                    if ($m['unit'] === 'Inches')
                        $dimensionIn = $m['value'];
                }
            }

            return [
                'passengerType' => $baggage['passengerTypeCodes'][0] ?? null,
                'type' => $baggage['baggageType'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'includedInOfferPrice' => ($item['includedInOfferPrice'] ?? '') === 'Yes',
                'weight' => [
                    'kg' => $weightKg,
                    'lb' => $weightLb,
                ],
                'dimension' => [
                    'cm' => $dimensionCm,
                    'in' => $dimensionIn,
                ],
                'text' => $item['Text'] ?? '',
            ];
        }, $baggageList);
    }

    protected function extractPenalties(array $penaltiesList): array
    {
        $penalties = $penaltiesList[0] ?? [];

        return [
            'changeNotPermitted' => $penalties['Change'][0]['NotPermittedInd'] ?? false,
            'cancelNotPermitted' => $penalties['Cancel'][0]['NotPermittedInd'] ?? false,
        ];
    }



    /**
     * Extracts sequence details for flight offers.
     *
     * @param array $combination Flight combination
     * @param string $offerId Offer identifier
     * @return array Sequence details
     */
    private function extractSequence(array $combination): array
    {
        $sequences = [];
        foreach ($combination as $key => $offer) {
            $sequences[$key] = [
                'sequence' => $key+1 ?? 0,
                'departure' => $offer['FlightSegment'][0]['Flight']['Departure']['location'] ?? [],
                'arrival' => end($offer['FlightSegment'])['Flight']['Arrival']['location'] ?? [],
                'total_duration' => SimplifyNumber::convertDurationToMinutes($offer['totalDuration'] ?? '0'),
                'service_class' => $this->extractPassengerFlights($offer['PassengerFlight'] ?? []),
                'flight_segments' => $this->getFlightSegments($offer['FlightSegment'] ?? []),
                'stops' => count($offer['FlightSegment'] ?? []),
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
     * Formats flight segment details.
     *
     * @param array $segments Flight segment data
     * @return array Formatted flight segments
     */
    private function getFlightSegments(array $segments): array
    {

        $flightSegments = [];
        foreach ($segments as $key => $segment) {
                $flightSegments[$key] = [
                    'carrier' => $segment['Flight']['carrier'] ?? 'Unknown',
                    'airline_imageUrl' => SimplifyNumber::airlineImageUrl($segment['Flight']['operatingCarrier']??null),
                    'flight_number' => $segment['Flight']['number'] ?? 'Unknown',
                    'equipment' => $segment['Flight']['equipment'] ?? 'Unknown',
                    'distance' => $segment['Flight']['distance'] ?? 0,
                    'duration' => SimplifyNumber::convertDurationToMinutes($segment['Flight']['duration'] ?? '0'),
                    'operating_carrier'=>$segment['Flight']['operatingCarrier']??'Unknown',
                    'operating_carrier_name'=>$segment['Flight']['operatingCarrierName']??'Unknown',
                    'departure' => [
                        'location' => $segment['Flight']['Departure']['location'] ?? 'Unknown',
                        'date' => $segment['Flight']['Departure']['date'] ?? 'Unknown',
                        'time' => $segment['Flight']['Departure']['time'] ?? 'Unknown',
                    ],
                    'arrival' => [
                        'location' => $segment['Flight']['Arrival']['location'] ?? 'Unknown',
                        'date' => $segment['Flight']['Arrival']['date'] ?? 'Unknown',
                        'time' => $segment['Flight']['Arrival']['time'] ?? 'Unknown',
                    ],
                ];            
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


 


}
