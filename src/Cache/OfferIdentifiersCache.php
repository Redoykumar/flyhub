<?php

namespace Redoy\FlyHub\Cache;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Redoy\FlyHub\Helpers\CacheKeyGenerator;

class OfferIdentifiersCache
{
    protected $defaultTtlMinutes;

    public function __construct(?int $defaultTtlMinutes = null)
    {
        $this->defaultTtlMinutes = $defaultTtlMinutes ?? config('flyhub.cache.offer_identifiers_ttl', 300);
    }

    public function store(string $searchId, array $offerIdentifiers, ?int $ttlMinutes = null): void
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        $ttl = $ttlMinutes ?? $this->defaultTtlMinutes;
        Cache::put($cacheKey, $offerIdentifiers, now()->addMinutes($ttl));
    }

    public function get(string $searchId): ?array
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::get($cacheKey);
    }

    // Original has() checks if offers exist for searchId
    public function has(string $searchId): bool
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::has($cacheKey);
    }

    public function forget(string $searchId): bool
    {
        $cacheKey = CacheKeyGenerator::generate($searchId, 'offer_identifiers');
        return Cache::forget($cacheKey);
    }

    /**
     * Check if offer identifiers exist in cache for a given search ID.
     */
    public function hasSearch(string $searchId): bool
    {
        return $this->has($searchId);
    }

    /**
     * Check if a specific offer ID exists in cache for a given search ID.
     */
    public function hasOffer(string $searchId, string $offerId): bool
    {
        $offerIdentifiers = $this->get($searchId);
        return !empty($offerIdentifiers[$offerId]);

    }
}
