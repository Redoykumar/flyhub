<?php

namespace Redoy\FlyHub\Providers\Amadeus\Services;

use Redoy\FlyHub\Contracts\Services\SearchServiceInterface;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Providers\Amadeus\AmadeusClient;
use Redoy\FlyHub\Providers\Amadeus\Transformers\SearchTransformer;

class SearchService implements SearchServiceInterface
{
    protected AmadeusClient $client;

    public function __construct(AmadeusClient $client)
    {
        $this->client = $client;
    }

    /**
     * Search for flights using Amadeus API and return standardized response.
     */
    public function search(SearchRequestDTO $request): SearchResponseDTO
    {

        $payload = $this->buildFromSearchRequest($request);



        $arrayVar = [
            "meta" => [
                "count" => 5,
                "links" => [
                    "self" =>
                        "https://test.api.amadeus.com/v2/shopping/flight-offers?originLocationCode=SYD&destinationLocationCode=BKK&departureDate=2025-05-16&returnDate=2025-05-30&adults=3&max=5&children=1&infants=2",
                ],
            ],
            "data" => [
                [
                    "type" => "flight-offer",
                    "id" => "1",
                    "source" => "GDS",
                    "instantTicketingRequired" => false,
                    "nonHomogeneous" => false,
                    "oneWay" => false,
                    "isUpsellOffer" => false,
                    "lastTicketingDate" => "2025-05-16",
                    "lastTicketingDateTime" => "2025-05-16",
                    "numberOfBookableSeats" => 9,
                    "itineraries" => [
                        [
                            "duration" => "PT16H25M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-16T11:25:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T18:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "802",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H25M",
                                    "id" => "1",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T22:20:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-17T00:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "843",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H30M",
                                    "id" => "2",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                        [
                            "duration" => "PT18H5M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-30T12:15:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T16:30:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "854",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H15M",
                                    "id" => "7",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T22:00:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-31T09:20:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "801",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H20M",
                                    "id" => "8",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                    ],
                    "price" => [
                        "currency" => "EUR",
                        "total" => "1803.94",
                        "base" => "711.00",
                        "fees" => [
                            ["amount" => "0.00", "type" => "SUPPLIER"],
                            ["amount" => "0.00", "type" => "TICKETING"],
                        ],
                        "grandTotal" => "1803.94",
                        "additionalServices" => [
                            ["amount" => "337.96", "type" => "CHECKED_BAGS"],
                        ],
                    ],
                    "pricingOptions" => [
                        "fareType" => ["PUBLISHED"],
                        "includedCheckedBagsOnly" => false,
                    ],
                    "validatingAirlineCodes" => ["MF"],
                    "travelerPricings" => [
                        [
                            "travelerId" => "1",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "2",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "3",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "4",
                            "fareOption" => "STANDARD",
                            "travelerType" => "CHILD",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "354.08",
                                "base" => "135.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "5",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "1",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "6",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "2",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "flight-offer",
                    "id" => "2",
                    "source" => "GDS",
                    "instantTicketingRequired" => false,
                    "nonHomogeneous" => false,
                    "oneWay" => false,
                    "isUpsellOffer" => false,
                    "lastTicketingDate" => "2025-05-16",
                    "lastTicketingDateTime" => "2025-05-16",
                    "numberOfBookableSeats" => 9,
                    "itineraries" => [
                        [
                            "duration" => "PT16H25M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-16T11:25:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T18:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "802",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H25M",
                                    "id" => "1",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T22:20:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-17T00:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "843",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H30M",
                                    "id" => "2",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                        [
                            "duration" => "PT28H30M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-30T01:50:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T06:15:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "844",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H25M",
                                    "id" => "9",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T22:00:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-31T09:20:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "801",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H20M",
                                    "id" => "10",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                    ],
                    "price" => [
                        "currency" => "EUR",
                        "total" => "1803.94",
                        "base" => "711.00",
                        "fees" => [
                            ["amount" => "0.00", "type" => "SUPPLIER"],
                            ["amount" => "0.00", "type" => "TICKETING"],
                        ],
                        "grandTotal" => "1803.94",
                        "additionalServices" => [
                            ["amount" => "337.96", "type" => "CHECKED_BAGS"],
                        ],
                    ],
                    "pricingOptions" => [
                        "fareType" => ["PUBLISHED"],
                        "includedCheckedBagsOnly" => false,
                    ],
                    "validatingAirlineCodes" => ["MF"],
                    "travelerPricings" => [
                        [
                            "travelerId" => "1",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "2",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "3",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "4",
                            "fareOption" => "STANDARD",
                            "travelerType" => "CHILD",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "354.08",
                                "base" => "135.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "5",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "1",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "6",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "2",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "1",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "2",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "flight-offer",
                    "id" => "3",
                    "source" => "GDS",
                    "instantTicketingRequired" => false,
                    "nonHomogeneous" => false,
                    "oneWay" => false,
                    "isUpsellOffer" => false,
                    "lastTicketingDate" => "2025-05-16",
                    "lastTicketingDateTime" => "2025-05-16",
                    "numberOfBookableSeats" => 9,
                    "itineraries" => [
                        [
                            "duration" => "PT26H50M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-16T11:25:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T18:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "802",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H25M",
                                    "id" => "5",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-17T08:50:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-17T11:15:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "853",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H25M",
                                    "id" => "6",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                        [
                            "duration" => "PT18H5M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-30T12:15:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T16:30:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "854",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H15M",
                                    "id" => "7",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T22:00:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-31T09:20:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "801",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H20M",
                                    "id" => "8",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                    ],
                    "price" => [
                        "currency" => "EUR",
                        "total" => "1803.94",
                        "base" => "711.00",
                        "fees" => [
                            ["amount" => "0.00", "type" => "SUPPLIER"],
                            ["amount" => "0.00", "type" => "TICKETING"],
                        ],
                        "grandTotal" => "1803.94",
                        "additionalServices" => [
                            ["amount" => "337.96", "type" => "CHECKED_BAGS"],
                        ],
                    ],
                    "pricingOptions" => [
                        "fareType" => ["PUBLISHED"],
                        "includedCheckedBagsOnly" => false,
                    ],
                    "validatingAirlineCodes" => ["MF"],
                    "travelerPricings" => [
                        [
                            "travelerId" => "1",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "2",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "3",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "4",
                            "fareOption" => "STANDARD",
                            "travelerType" => "CHILD",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "354.08",
                                "base" => "135.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "5",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "1",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "6",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "2",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "7",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "8",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "flight-offer",
                    "id" => "4",
                    "source" => "GDS",
                    "instantTicketingRequired" => false,
                    "nonHomogeneous" => false,
                    "oneWay" => false,
                    "isUpsellOffer" => false,
                    "lastTicketingDate" => "2025-05-16",
                    "lastTicketingDateTime" => "2025-05-16",
                    "numberOfBookableSeats" => 9,
                    "itineraries" => [
                        [
                            "duration" => "PT26H50M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-16T11:25:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-16T18:50:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "802",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H25M",
                                    "id" => "5",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-17T08:50:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-17T11:15:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "853",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H25M",
                                    "id" => "6",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                        [
                            "duration" => "PT28H30M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-30T01:50:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T06:15:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "844",
                                    "aircraft" => ["code" => "738"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT3H25M",
                                    "id" => "9",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "XMN",
                                        "terminal" => "3",
                                        "at" => "2025-05-30T22:00:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-31T09:20:00",
                                    ],
                                    "carrierCode" => "MF",
                                    "number" => "801",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "MF"],
                                    "duration" => "PT9H20M",
                                    "id" => "10",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                    ],
                    "price" => [
                        "currency" => "EUR",
                        "total" => "1803.94",
                        "base" => "711.00",
                        "fees" => [
                            ["amount" => "0.00", "type" => "SUPPLIER"],
                            ["amount" => "0.00", "type" => "TICKETING"],
                        ],
                        "grandTotal" => "1803.94",
                        "additionalServices" => [
                            ["amount" => "337.96", "type" => "CHECKED_BAGS"],
                        ],
                    ],
                    "pricingOptions" => [
                        "fareType" => ["PUBLISHED"],
                        "includedCheckedBagsOnly" => false,
                    ],
                    "validatingAirlineCodes" => ["MF"],
                    "travelerPricings" => [
                        [
                            "travelerId" => "1",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "2",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "3",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "460.14",
                                "base" => "180.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "includedCheckedBags" => ["quantity" => 1],
                                    "includedCabinBags" => ["quantity" => 1],
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "4",
                            "fareOption" => "STANDARD",
                            "travelerType" => "CHILD",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "354.08",
                                "base" => "135.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "5",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "1",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "6",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "2",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "34.72",
                                "base" => "18.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "5",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "6",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "9",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "segmentId" => "10",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "S1GVAAUS",
                                    "brandedFare" => "YACCOMPANY",
                                    "brandedFareLabel" => "ECONOMY ACCOMPANIED TRAVEL",
                                    "class" => "S",
                                    "amenities" => [
                                        [
                                            "description" =>
                                                "CHECKED BAG 1PC OF 23KG 158CM",
                                            "isChargeable" => false,
                                            "amenityType" => "BAGGAGE",
                                            "amenityProvider" => [
                                                "name" => "BrandedFare",
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "type" => "flight-offer",
                    "id" => "5",
                    "source" => "GDS",
                    "instantTicketingRequired" => false,
                    "nonHomogeneous" => false,
                    "oneWay" => false,
                    "isUpsellOffer" => false,
                    "lastTicketingDate" => "2025-05-08",
                    "lastTicketingDateTime" => "2025-05-08",
                    "numberOfBookableSeats" => 5,
                    "itineraries" => [
                        [
                            "duration" => "PT15H35M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-16T21:45:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "CAN",
                                        "terminal" => "2",
                                        "at" => "2025-05-17T05:25:00",
                                    ],
                                    "carrierCode" => "CZ",
                                    "number" => "302",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "CZ"],
                                    "duration" => "PT9H40M",
                                    "id" => "3",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "CAN",
                                        "terminal" => "2",
                                        "at" => "2025-05-17T08:15:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-17T10:20:00",
                                    ],
                                    "carrierCode" => "CZ",
                                    "number" => "357",
                                    "aircraft" => ["code" => "7M8"],
                                    "operating" => ["carrierCode" => "CZ"],
                                    "duration" => "PT3H5M",
                                    "id" => "4",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                        [
                            "duration" => "PT13H40M",
                            "segments" => [
                                [
                                    "departure" => [
                                        "iataCode" => "BKK",
                                        "at" => "2025-05-30T03:00:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "CAN",
                                        "terminal" => "2",
                                        "at" => "2025-05-30T07:00:00",
                                    ],
                                    "carrierCode" => "CZ",
                                    "number" => "3036",
                                    "aircraft" => ["code" => "7M8"],
                                    "operating" => ["carrierCode" => "CZ"],
                                    "duration" => "PT3H",
                                    "id" => "11",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                                [
                                    "departure" => [
                                        "iataCode" => "CAN",
                                        "terminal" => "2",
                                        "at" => "2025-05-30T08:05:00",
                                    ],
                                    "arrival" => [
                                        "iataCode" => "SYD",
                                        "terminal" => "1",
                                        "at" => "2025-05-30T19:40:00",
                                    ],
                                    "carrierCode" => "CZ",
                                    "number" => "301",
                                    "aircraft" => ["code" => "789"],
                                    "operating" => ["carrierCode" => "CZ"],
                                    "duration" => "PT9H35M",
                                    "id" => "12",
                                    "numberOfStops" => 0,
                                    "blacklistedInEU" => false,
                                ],
                            ],
                        ],
                    ],
                    "price" => [
                        "currency" => "EUR",
                        "total" => "2052.66",
                        "base" => "1273.00",
                        "fees" => [
                            ["amount" => "0.00", "type" => "SUPPLIER"],
                            ["amount" => "0.00", "type" => "TICKETING"],
                        ],
                        "grandTotal" => "2052.66",
                    ],
                    "pricingOptions" => [
                        "fareType" => ["PUBLISHED"],
                        "includedCheckedBagsOnly" => false,
                    ],
                    "validatingAirlineCodes" => ["CZ"],
                    "travelerPricings" => [
                        [
                            "travelerId" => "1",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "523.82",
                                "base" => "322.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "Z",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "A",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "2",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "523.82",
                                "base" => "322.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "Z",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "A",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "3",
                            "fareOption" => "STANDARD",
                            "travelerType" => "ADULT",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "523.82",
                                "base" => "322.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "Z",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPX",
                                    "class" => "A",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPX",
                                    "class" => "V",
                                    "includedCheckedBags" => ["quantity" => 2],
                                    "includedCabinBags" => ["quantity" => 1],
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "4",
                            "fareOption" => "STANDARD",
                            "travelerType" => "CHILD",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "381.76",
                                "base" => "241.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXCH25",
                                    "class" => "Z",
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXCH25",
                                    "class" => "A",
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXCH25",
                                    "class" => "V",
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXCH25",
                                    "class" => "V",
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "5",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "1",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "49.72",
                                "base" => "33.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXIN90",
                                    "class" => "Z",
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXIN90",
                                    "class" => "A",
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXIN90",
                                    "class" => "V",
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXIN90",
                                    "class" => "V",
                                ],
                            ],
                        ],
                        [
                            "travelerId" => "6",
                            "fareOption" => "STANDARD",
                            "travelerType" => "HELD_INFANT",
                            "associatedAdultId" => "2",
                            "price" => [
                                "currency" => "EUR",
                                "total" => "49.72",
                                "base" => "33.00",
                            ],
                            "fareDetailsBySegment" => [
                                [
                                    "segmentId" => "3",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXIN90",
                                    "class" => "Z",
                                ],
                                [
                                    "segmentId" => "4",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "Z2APAYPXIN90",
                                    "class" => "A",
                                ],
                                [
                                    "segmentId" => "11",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXIN90",
                                    "class" => "V",
                                ],
                                [
                                    "segmentId" => "12",
                                    "cabin" => "ECONOMY",
                                    "fareBasis" => "V2LSRYPXIN90",
                                    "class" => "V",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            "dictionaries" => [
                "locations" => [
                    "CAN" => ["cityCode" => "CAN", "countryCode" => "CN"],
                    "BKK" => ["cityCode" => "BKK", "countryCode" => "TH"],
                    "XMN" => ["cityCode" => "XMN", "countryCode" => "CN"],
                    "SYD" => ["cityCode" => "SYD", "countryCode" => "AU"],
                ],
                "aircraft" => [
                    "738" => "BOEING 737-800",
                    "789" => "BOEING 787-9",
                    "7M8" => "BOEING 737 MAX 8",
                ],
                "currencies" => ["EUR" => "EURO"],
                "carriers" => [
                    "CZ" => "CHINA SOUTHERN AIRLINES",
                    "MF" => "XIAMEN AIRLINES",
                ],
            ],
        ];




        // $response = $this->client
        //     ->request('POST', '/shopping/flight-offers')
        //     ->withBody($payload)
        //     ->send();
        // dd($response->json());
        // $transformedData = (new SearchTransformer($response->json(), $request))->transform();
        $transformedData = (new SearchTransformer($arrayVar, $request))->transform();

        return new SearchResponseDTO([
            [
                'provider' => $transformedData['provider']??[],
                'flights' => $transformedData['flights']??[],
                'meta' => $transformedData['meta']??[],
            ]
        ]);
    }

    /**
     * Build the request body for the Amadeus flight search.
     */
    protected function buildFromSearchRequest(SearchRequestDTO $request): array
    {
        // === Travelers ===
        $travelers = [];
        $travelerId = 1;

        for ($i = 0; $i < $request->getPassengers()['adults']; $i++) {
            $travelers[] = [
                'id' => (string) $travelerId++,
                'travelerType' => 'ADULT',
                'fareOptions' => ['STANDARD']
            ];
        }

        for ($i = 0; $i < $request->getPassengers()['children']; $i++) {
            $travelers[] = [
                'id' => (string) $travelerId++,
                'travelerType' => 'CHILD',
                'fareOptions' => ['STANDARD']
            ];
        }

        // === Origin/Destination Segments ===
        $originDestinations = [
            [
                'id' => '1',
                'originLocationCode' => $request->getOrigin(),
                'destinationLocationCode' => $request->getDestination(),
                'departureDateTimeRange' => [
                    'date' => $request->getDepartureDate(),
                    'time' => '10:00:00'
                ]
            ]
        ];

        if ($request->getTripType() === 'round-trip') {
            $originDestinations[] = [
                'id' => '2',
                'originLocationCode' => $request->getDestination(),
                'destinationLocationCode' => $request->getOrigin(),
                'departureDateTimeRange' => [
                    'date' => $request->getReturnDate(),
                    'time' => '17:00:00'
                ]
            ];
        }

        return [
            'currencyCode' => 'USD',
            'originDestinations' => $originDestinations,
            'travelers' => $travelers,
            'sources' => ['GDS'],
            'searchCriteria' => [
                'maxFlightOffers' => 2,
                'flightFilters' => [
                    'cabinRestrictions' => [
                        [
                            'cabin' => 'BUSINESS',
                            'coverage' => 'MOST_SEGMENTS',
                            'originDestinationIds' => ['1']
                        ]
                    ],
                    'carrierRestrictions' => [
                        'excludedCarrierCodes' => ['AA', 'TP', 'AZ']
                    ]
                ]
            ]
        ];
    }

}
