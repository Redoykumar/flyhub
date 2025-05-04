<?php

namespace Redoy\FlyHub\Cache;

use Redoy\FlyHub\DTOs\Requests\SearchRequestDTO;
use Illuminate\Support\Facades\Cache;

class SearchCache
{
    /**
     * Check if a search result exists in the cache.
     *
     * @param SearchRequestDTO $dto
     * @return bool
     */
    public function has(SearchRequestDTO $dto): bool
    {
        if (!$this->isCacheEnabled()) {
            return false;
        }

        return Cache::has($this->generateCacheKey($dto));
    }

    /**
     * Retrieve a search result from the cache.
     *
     * @param SearchRequestDTO $dto
     * @return array|null
     */
    public function get(SearchRequestDTO $dto): ?array
    {
        if (!$this->isCacheEnabled()) {
            return null;
        }

        return Cache::get($this->generateCacheKey($dto));
    }

    /**
     * Store a search result in the cache.
     *
     * @param SearchRequestDTO $dto
     * @param array $results
     * @return void
     */
    public function put(SearchRequestDTO $dto, array $results): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $ttl = config('flyhub.cache.ttl', 3600);
        Cache::put($this->generateCacheKey($dto), $results, $ttl);
    }

    /**
     * Generate a unique cache key based on SearchRequestDTO attributes.
     *
     * @param SearchRequestDTO $dto
     * @return string
     */
    protected function generateCacheKey(SearchRequestDTO $dto): string
    {
        $params = [
            'trip_type' => $dto->getTripType(),
            'passengers' => $dto->getPassengers(),
            'segments' => array_map(function ($segment) {
                return [
                    'from' => $segment['from'] ?? '',
                    'to' => $segment['to'] ?? '',
                    'date' => $segment['date'] ?? '',
                    'stops' => $segment['stops'] ?? '',
                ];
            }, $dto->getSegments()),
            'cabin' => $dto->getModifiers()['cabin'] ?? 'economy',
            'direct_only' => $dto->isDirectOnly(),
            'flex_dates' => $dto->hasFlexibleDates(),
            'currency' => $dto->getPricing()['currency'] ?? 'USD',
        ];

        // Create a deterministic string from parameters
        $key = 'flyhub_search_' . md5(serialize($params));
        return $key;
    }

    /**
     * Check if caching is enabled in the configuration.
     *
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return config('flyhub.cache.enabled', false);
    }
}