<?php

namespace Redoy\FlyHub\Helpers;

class CacheKeyGenerator
{
    /**
     * Generate a unique cache key based on a prefix and identifier.
     *
     * @param string $identifier Unique identifier (e.g., search ID)
     * @param string $prefix Cache type prefix (e.g., 'offer_identifiers', 'price', 'search')
     * @return string
     */
    public static function generate(string $identifier, string $prefix): string
    {
        return "flyhub:{$prefix}:{$identifier}";
    }

    /**
     * Generate a cache key for offer identifiers.
     *
     * @param string $searchId
     * @return string
     */
    public static function forOfferIdentifiers(string $searchId): string
    {
        return self::generate($searchId, 'offer_identifiers');
    }

    /**
     * Generate a cache key for price data.
     *
     * @param string $searchId
     * @return string
     */
    public static function forPrice(string $searchId): string
    {
        return self::generate($searchId, 'price');
    }

    /**
     * Generate a cache key for search data.
     *
     * @param string $searchId
     * @return string
     */
    public static function forSearch(string $searchId): string
    {
        return self::generate($searchId, 'search');
    }

    /**
     * Generate a cache key for token data.
     *
     * @param string $provider
     * @return string
     */
    public static function forToken(string $provider): string
    {
        return self::generate($provider, 'token');
    }
}