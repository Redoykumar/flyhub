<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;

class SearchTransformer
{
    protected array $data;
    protected array $flight;
    protected array $product;
    protected array $conditions;
    protected array $brand;
    protected array $offering;

    public function __construct(array $data, SearchRequestDTO $request)
    {
        $this->data = $data;
        // dd($data);
        $this->flight = array_column(array_column(
            $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
            null,
            '@type'
        )['ReferenceListFlight']['Flight'], null, 'id');
        $this->product = array_column(array_column(
            $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
            null,
            '@type'
        )['ReferenceListProduct']['Product'], null, 'id');
        $this->conditions = array_column(array_column(
            $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
            null,
            '@type'
        )['ReferenceListTermsAndConditions']['TermsAndConditions'], null, 'id');
        $this->brand = array_column(array_column(
            $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
            null,
            '@type'
        )['ReferenceListBrand']['Brand'], null, 'id');

        $this->offering =  $this->data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'][0];
       

    }

    public function transform(): array
    {
        dd($this->offering);
        $value = [
            'provider' => 'Travelport',
            'flights' => $this->transformFlights(),
            'meta' => $this->transformMeta(),
        ];

        dd($value);  // Debugging output
        return $value;
    }

    protected function transformFlights(): array
    {

        return array_map(function ($flight) {
            return [
                'id' => $flight['id'],
                'fare_type' => $flight['fare_type'] ?? 1,
                'total_duration' => $flight['totalDuration'] ?? 1,
                'stops' => $flight['stops'] ?? 0,
                'passenger' => $flight['PassengerFlight'] ?? 0,
                'price' => $this->transformPrice($flight['price'] ?? []),
                'segments' => $this->transformSegments($flight['FlightSegment'] ?? []),
                'conditions' => $this->transformConditions($flight['conditions'] ?? []),
                'in_flight_amenities' => $flight['in_flight_amenities'] ?? [],
            ];
        }, $product ?? []);
    }

    protected function transformPrice(array $price): array
    {
        return [
            'amount' => $price['amount'] ?? 0,
            'currency' => $price['currency'] ?? 'USD',
            'breakdown' => [
                'base' => $price['breakdown']['base'] ?? 0,
                'tax' => $price['breakdown']['tax'] ?? 0,
            ],
        ];
    }

    protected function transformSegments(array $segments): array
    {
        $flight = array_column(array_column(
            $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
            null,
            '@type'
        )['ReferenceListFlight']['Flight'], null, 'id');

        return array_map(function ($segment) use ($flight) {
            $value = $flight[$segment['Flight']['FlightRef']];
            return [
                'segment_number' => $value['id'] ?? null,
                'from' => $this->transformLocation($value['Departure'] ?? []),
                'to' => $this->transformLocation($value['Arrival'] ?? []),
                'distance' => $value['distance'] ?? [],
                'flight_number' => $value['number'] ?? null,
                'airline' => $value['carrier'] ?? [],
                'aircraft' => $value['aircraft'] ?? null,
                'duration' => $value['duration'] ?? null,
                'stops' => $flight['stops'] ?? 0,
                'flight_class' => $value['flight_class'] ?? null,
                'cabin_type' => $value['equipment'] ?? null,
            ];
        }, $segments);
    }

    protected function transformLocation(array $location): array
    {
        return [
            'airport' => $location['location'] ?? null,
            'terminal' => $location['terminal'] ?? null,
            'date' => $location['date'] ?? null,
            'time' => $location['time'] ?? null,
        ];
    }

    protected function transformAirline(array $airline): array
    {
        return [
            'code' => $airline['code'] ?? null,
            'name' => $airline['name'] ?? null,
            'icon' => $airline['icon'] ?? null,
        ];
    }

    protected function transformLayover(array $layover): array
    {
        return [
            'duration' => $layover['duration'] ?? null,
            'location' => $layover['location'] ?? null,
        ];
    }

    protected function transformConditions(array $conditions): array
    {
        return [
            'is_refundable' => $conditions['is_refundable'] ?? false,
            'baggage' => $this->transformBaggage($conditions['baggage'] ?? []),
            'terms_and_conditions' => $this->transformTermsAndConditions($conditions['terms_and_conditions'] ?? []),
        ];
    }

    protected function transformBaggage(array $baggage): array
    {
        return [
            'checked' => $baggage['checked'] ?? null,
            'carry_on' => $baggage['carry_on'] ?? null,
        ];
    }

    protected function transformTermsAndConditions(array $terms): array
    {
        return [
            'cancellation_policy' => $terms['cancellation_policy'] ?? null,
            'change_policy' => $terms['change_policy'] ?? null,
        ];
    }

    protected function transformMeta(): array
    {
        $meta = $this->data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'][0] ?? [];
        return [
            'search_id' => $meta['id'] ?? null,
            'origin' => $meta['Departure'] ?? null,
            'destination' => $meta['Arrival'] ?? null,
            'departure_date' => $meta['departure_date'] ?? null,
            'trip_type' => $meta['trip_type'] ?? false,
            'currency' => $meta['currency'] ?? 'USD',
            'total_results' => count($meta['ProductBrandOptions']) ?? 0,
        ];
    }
}
