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
                $overallStart = microtime(true);
                echo "🔍 Search started...\n\n";

                $results = [];
                $markupManager = new MarkupManager();
                $providers = config('flyhub.providers', []);

                $initEnd = microtime(true);
                echo "🛠️  Initialization completed in: " . number_format($initEnd - $overallStart, 4) . " seconds\n\n";

                foreach ($providers as $providerName => $providerConfig) {
                    echo "➡️  Processing provider: {$providerName}\n";
                    $providerStart = microtime(true);

                    $clientClass = $providerConfig['client'];
                    $searchServiceClass = $providerConfig['search'];

                    if (!class_exists($clientClass) || !class_exists($searchServiceClass)) {
                        throw new \Exception("❌ Client or search service class not found for provider {$providerName}.");
                    }

                    // Instantiate client and search service
                    $client = new $clientClass($providerConfig);
                    $searchService = new $searchServiceClass($client);

                    // Perform search
                    $searchStart = microtime(true);
                    $providerResponse = $searchService->search($this->dto);
                    $searchEnd = microtime(true);
                    echo "   🔎 Search completed in: " . number_format($searchEnd - $searchStart, 4) . " seconds\n";

                    // Apply markup
                    $providerResults = $providerResponse->data[0]['data'] ?? [];
                    $markupStart = microtime(true);
                    $providerResults = $markupManager->applyMarkupToFlights($providerResults, $providerName);
                    $markupEnd = microtime(true);
                    echo "   💲 Markup applied in: " . number_format($markupEnd - $markupStart, 4) . " seconds\n";

                    $results = array_merge($results, $providerResults);
                    $providerEnd = microtime(true);
                    echo "✅ Provider {$providerName} processed in: " . number_format($providerEnd - $providerStart, 4) . " seconds\n\n";
                }

                // Sorting
                $sortingStart = microtime(true);
                foreach ($this->sorters as $sorter) {
                    $results = $sorter->apply($results);
                }
                $sortingEnd = microtime(true);
                echo "📊 Sorting completed in: " . number_format($sortingEnd - $sortingStart, 4) . " seconds\n";

                // Store results
                $setResultsStart = microtime(true);
                $this->manager->setResults($results, []);
                $setResultsEnd = microtime(true);
                echo "🗃️  Result storing completed in: " . number_format($setResultsEnd - $setResultsStart, 4) . " seconds\n";

                // Final response creation
                $response = (new SearchResponseDTO($results, []))->toArray();

                $overallEnd = microtime(true);
                echo "\n🏁 Total execution time: " . number_format($overallEnd - $overallStart, 4) . " seconds\n";

                return $response;
            }


        };
    }
}