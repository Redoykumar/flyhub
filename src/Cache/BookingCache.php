<?php

namespace Redoy\FlyHub\Cache;

use Illuminate\Support\Facades\Cache;
use Redoy\FlyHub\DTOs\Responses\BookingResponseDTO;

class BookingCache
{
    private const PREFIX = 'booking_';
    private const TTL = 3600;

    /**
     * Generate the cache key using the booking ID.
     */
    private function getKey(string $bookingId): string
    {
        return self::PREFIX . $bookingId;
    }

    /**
     * Store booking data in the cache.
     */
    public function put(string $bookingId, array $data, ?int $ttl = null): void
    {
        Cache::put($this->getKey($bookingId), $data, $ttl ?? self::TTL);
    }

    /**
     * Retrieve booking data from the cache.
     * Returns an empty array if not found.
     */
    public function get(string $bookingId): array
    {
        return Cache::get($this->getKey($bookingId), []);
    }

    /**
     * Check if booking data exists in the cache.
     */
    public function has(string $bookingId): bool
    {
        return Cache::has($this->getKey($bookingId));
    }

    /**
     * Remove booking data from the cache.
     */
    public function forget(string $bookingId): bool
    {
        return Cache::forget($this->getKey($bookingId));
    }
    public function setCacheValue(array $values)
    {
        foreach ($values as $key => $value) {
            Cache::put($this->getKey($key), $value, self::TTL);
        }
    }
}
