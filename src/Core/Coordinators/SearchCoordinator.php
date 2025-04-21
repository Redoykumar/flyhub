<?php

namespace Redoy\FlyHub\Core\Coordinators;

use Redoy\FlyHub\Core\FlyHubManager;

class SearchCoordinator
{
    // Reference to FlyHubManager instance
    protected $manager;

    // Constructor injects FlyHubManager
    public function __construct(FlyHubManager $manager)
    {
        $this->manager = $manager;
    }

    // Initiates a search and returns a fluent chainable object
    public function search($request)
    {
        return new class ($this->manager, $request) {
            protected $manager;
            protected $request;

            // Constructor stores manager and request
            public function __construct($manager, $request)
            {
                $this->manager = $manager;
                $this->request = $request;
            }

            // Placeholder for filter logic
            public function filters()
            {
                return $this;
            }

            // Placeholder for sort logic
            public function sort()
            {
                return $this;
            }

            // Executes the search and retrieves results
            public function get()
            {
                // Create provider client instance (e.g., TravelportClient or AmadeusClient)
                $client = new ($this->manager->getProviderClass())($this->manager->getConfig());
                // Extract provider name (e.g., 'Travelport' or 'Amadeus')
                $providerNamespace = explode('\\', $this->manager->getProviderClass())[3];
                // Construct correct SearchService namespace
                $searchServiceClass = "Redoy\\FlyHub\\Providers\\{$providerNamespace}\\Services\\SearchService";
                // Instantiate SearchService with client
                $searchService = new $searchServiceClass($client);
                // Call search with request and store results
                $results = $searchService->search($this->request);
                // Save results to manager
                $this->manager->setResults($results);
                // Return results
                return $this->manager->getResults();
            }
        };
    }
}