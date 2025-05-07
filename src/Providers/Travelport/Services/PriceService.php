<?php
namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\Contracts\Services\PriceServiceInterface;
use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;


class PriceService implements PriceServiceInterface
{
    protected $client;
    protected $transformer;

    public function __construct(TravelportClient $client)
    {
        $this->client = $client;

    }


    protected function buildBody(array $request): array
    {
        return [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequest' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'validateInventoryInd' => true,
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => [
                            'value' => $request['CatalogProductOfferingsIdentifier'],
                        ],
                    ],
                    'CatalogProductOfferingSelection' => array_map(function ($product) {
                        return [
                            'CatalogProductOfferingIdentifier' => [
                                'Identifier' => [
                                    'value' => $product['CatalogProductOfferingIdentifier'],
                                ],
                            ],
                            'ProductIdentifier' => [
                                [
                                    'Identifier' => [
                                        'value' => $product['ProductIdentifier'],
                                    ],
                                ],
                            ],
                        ];
                    }, $request['products']),
                ],
            ],
        ];
    }



    /**
     * Process the pricing request to Travelport API.
     *
     * @param array $request
     * @return PriceResponseDTO
     */
    public function price(array $request): PriceResponseDTO
    {

        // dd($request);
        $payload = $this->buildBody($request);
        $data = [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequest' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => [
                            'value' => 'b06fb4ab-07e4-4d6c-a11a-44f670d85374',
                        ],
                    ],
                    'CatalogProductOfferingSelection' => [
                        [
                            'CatalogProductOfferingIdentifier' => [
                                'Identifier' => [
                                    'value' => 'EK_CPO0',
                                ],
                            ],
                            'ProductIdentifier' => [
                                [
                                    'Identifier' => [
                                        'value' => 'EKp0',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'validateInventoryInd' => true,
                ],
            ],
        ];
        // Send the request to Travelport API
        $response = $this->client
            ->request('POST', '/price/offers/buildfromcatalogproductofferings')
            ->withBody($data)
            ->send();

        dd($response->json());
        // Check if the response is successful
        if (!$response->successful()) {
            throw new \Exception('Travelport API pricing request failed: ' . $response->body());
        }

        // Transform the response data into a DTO
        $priceResponse = $this->transformer->transform($response->json());

        return $priceResponse;
    }
}
