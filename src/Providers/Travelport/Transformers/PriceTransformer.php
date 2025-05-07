<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;

class PriceTransformer
{
    /**
     * Transform the raw Travelport API response into a PriceResponseDTO.
     *
     * @param array $response
     * @return PriceResponseDTO
     */
    public function transform(array $response): PriceResponseDTO
    {
        // Check if the response contains pricing data
        if (!isset($response['PricingInfo']) || empty($response['PricingInfo'])) {
            throw new \Exception('No pricing information found in the response.');
        }

        // Extract the relevant pricing information from the response
        $pricingInfo = $response['PricingInfo'][0];  // Assuming there's at least one pricing option

        // Initialize the PriceResponseDTO with the relevant data
        $priceResponse = new PriceResponseDTO([
            'totalPrice' => $pricingInfo['TotalPrice']['Amount'], // Example of total price
            'currency' => $pricingInfo['TotalPrice']['CurrencyCode'], // Example of currency code
            'baseFare' => $pricingInfo['BaseFare']['Amount'], // Example of base fare
            'taxes' => $pricingInfo['Taxes']['Amount'], // Example of taxes
            'flightDetails' => $this->getFlightDetails($pricingInfo), // Extract flight details
        ]);

        return $priceResponse;
    }

    /**
     * Helper method to extract flight details.
     *
     * @param array $pricingInfo
     * @return array
     */
    private function getFlightDetails(array $pricingInfo): array
    {
        $flightDetails = [];

        // Extracting flight details like origin, destination, departure time, etc.
        foreach ($pricingInfo['FlightSegment'] as $segment) {
            $flightDetails[] = [
                'origin' => $segment['DepartureAirport']['LocationCode'],
                'destination' => $segment['ArrivalAirport']['LocationCode'],
                'departureTime' => $segment['DepartureDateTime'],
                'arrivalTime' => $segment['ArrivalDateTime'],
                'airline' => $segment['OperatingAirline']['Code'],
                'flightNumber' => $segment['FlightNumber'],
            ];
        }

        return $flightDetails;
    }
}
