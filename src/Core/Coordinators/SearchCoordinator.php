<?php

namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Core\FlyHubManager;
use Redoy\FlyHub\DTOs\Responses\SearchResponseDTO;
use Redoy\FlyHub\Filters\PriceFilter;
use Redoy\FlyHub\Sorters\PriceSorter;
use Redoy\FlyHub\Markup\MarkupManager;

class SearchCoordinator
{
    protected $manager;

    public function __construct(FlyHubManager $manager)
    {
        $this->manager = $manager;
    }

    public function search($dto)
    {
        return new class ($this->manager, $dto) {
            protected $manager;
            protected $dto;
            protected $filters = [];
            protected $sorters = [];

            public function __construct($manager, $dto)
            {
                $this->manager = $manager;
                $this->dto = $dto;
            }

            public function filters()
            {
                $this->filters[] = new PriceFilter($this->dto->price_range ?? ['min' => 0, 'max' => PHP_INT_MAX]);
                return $this;
            }

            public function sort()
            {
                $this->sorters[] = new PriceSorter();
                return $this;
            }

            public function get()
            {
                $results = [];
                $markupManager = new MarkupManager();
                $providers = config('flyhub.providers', []);

                foreach ($providers as $providerName => $providerConfig) {
                    $clientClass = $providerConfig['client'];
                    $searchServiceClass = $providerConfig['search'];

                    if (!class_exists($clientClass) || !class_exists($searchServiceClass)) {
                        throw new \Exception("Client or search service class not found for provider {$providerName}.");
                    }

                    $client = new $clientClass($providerConfig);
                    $searchService = new $searchServiceClass($client);
                    $providerResponse = $searchService->search($this->dto);
                    $providerResults = $providerResponse->data[0]['data'] ?? [];
                    $providerResults = $markupManager->applyMarkupToFlights($providerResults, $providerName);
                    $results = array_merge($results, $providerResults);
                }

                foreach ($this->sorters as $sorter) {
                    $results = $sorter->apply($results);
                }

                $this->manager->setResults($results, []);
                return (new SearchResponseDTO($results, []))->toArray();
            }
        };
    }
}