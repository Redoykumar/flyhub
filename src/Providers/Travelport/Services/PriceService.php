<?php
namespace Redoy\FlyHub\Providers\Travelport\Services;

use Redoy\FlyHub\DTOs\Requests\PriceRequestDTO;
use Redoy\FlyHub\DTOs\Responses\PriceResponseDTO;
use Redoy\FlyHub\Providers\Travelport\TravelportClient;
use Redoy\FlyHub\Contracts\Services\PriceServiceInterface;
use Redoy\FlyHub\Providers\Travelport\Transformers\PriceTransformer;


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


        $payload = $this->buildBody($request);


    
        // Send the request to Travelport API
        $response = $this->client
        ->request('POST', '/price/offers/buildfromcatalogproductofferings')
        ->withBody($payload)
        ->send();
        

        // Check if the response is successful
        if (!$response->successful()) {
            throw new \Exception('Travelport API pricing request failed: ' . $response->body());
        }

        // Transform the response data into a DTO

        $priceResponse = (new PriceTransformer($response->json(), $request))->transform()??[];

        return $priceResponse;
    }
}
