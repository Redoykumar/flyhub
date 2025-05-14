<?php

namespace Redoy\FlyHub\Cache;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Redoy\FlyHub\Helpers\CacheKeyGenerator;

class OfferIdentifiersCache
{
    /**
     * Default TTL for cache entries in minutes.
     *
     * @var int
     */
    protected $defaultTtlMinutes;

    /**
     * Constructor to initialize the cache with a default TTL.
     *
     * @param int|null $defaultTtlMinutes
     */
    public function __construct(?int $defaultTtlMinutes = null)
    {
        $this->defaultTtlMinutes = $defaultTtlMinutes ?? config('flyhub.cache.offer_identifiers_ttl', 25);
    }

    /**
     * Store offer identifiers in cache for a given search ID.
     *
     * @param string $searchId
     * @param array $offerIdentifiers
     * @param int|null $ttlMinutes
     * @return void
     */
    public function store(string $searchId, array $offerIdentifiers, ?int $ttlMinutes = null): void
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        $ttl = $ttlMinutes ?? $this->defaultTtlMinutes;
        Cache::put($cacheKey, $offerIdentifiers, now()->addMinutes($ttl));
    }

    /**
     * Retrieve offer identifiers from cache for a given search ID.
     *
     * @param string $searchId
     * @return array|null
     */
    public function get(string $searchId): ?array
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::get($cacheKey);
    }

    /**
     * Check if offer identifiers exist in cache for a given search ID.
     *
     * @param string $searchId
     * @return bool
     */
    public function has(string $searchId): bool
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::has($cacheKey);
    }

    /**
     * Remove offer identifiers from cache for a given search ID.
     *
     * @param string $searchId
     * @return bool
     */
    public function forget(string $searchId): bool
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::forget($cacheKey);
    }
}