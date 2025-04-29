<?php
namespace Redoy\FlyHub\Providers\Travelport\Transformers;

use Illuminate\Support\Facades\Log;
use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;

class SearchTransformer
{
    protected array $data;
    protected array $flight;
    protected array $product;
    protected array $conditions;
    protected array $brand;
    protected array $offering;
    protected array $modOffering;
    protected array $CombinabilityCodeByOffer;

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
        // $this->brand = array_column(array_column(
        //     $this->data['CatalogProductOfferingsResponse']['ReferenceList'],
        //     null,
        //     '@type'
        // )['ReferenceListBrand']['Brand'], null, 'id');

        $this->offering = $this->data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'];
        // $this->modOffering=


    }

    public function transform(): array
    {
        // dd($this->data);
        // dd($this->getCatalogProductOffering($this->data));
        foreach ($this->getCatalogProductOffering($this->data)[1][1] as $key => $value) {
            $this->getProductByCombinabilityCode([$key=>$value]);
        }
        dd($this->CombinabilityCodeByOffer);
    }
    function getProductByCombinabilityCode($codeWithData)
    {

        $offer=[];
        foreach ($this->CombinabilityCodeByOffer as $key => $value) {
            $offer[$key] = $value[array_key_first($codeWithData)];
        }
        $this->getCombinSequence($offer);
    }

    function getCombinSequence($offer)
    {
        // $this->getCombinSequence($offer);
    }

    function getCatalogProductOffering($data)
    {
        $offering = $data['CatalogProductOfferingsResponse']['CatalogProductOfferings']['CatalogProductOffering'];
        $offerListbysequence = [];
        $CombinabilityCodeByOffer = [];
        foreach ($offering as $key => $cpor) {
            foreach ($cpor['ProductBrandOptions'] as $key => $pbo) {
                foreach ($pbo['ProductBrandOffering'] as $key => $pboff) {
                    $pboff['sequence'] = $cpor['sequence'];
                    $offerListbysequence[$cpor['sequence']][]= $pboff;
                    foreach ($pboff['CombinabilityCode'] as $key => $value) {
                        $pboff['p']= $pboff['Product'][0]['productRef'];
                        $CombinabilityCodeByOffer[$pboff['sequence']][$value][] = $pboff;
                    }
                }
            }
        }
        $this->CombinabilityCodeByOffer=$CombinabilityCodeByOffer;
        return [$offerListbysequence, $CombinabilityCodeByOffer];


    }
    function getCatalogProductOfferingBysequence()
    {

    }
}
