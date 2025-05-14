<?php

namespace Redoy\FlyHub\Cache;

use Illuminate\Support\Facades\Cache;

class PriceCache
{
    protected $prefix = 'flyhub_price_';
    protected $ttl = 24;

    public function has(string $offerId): bool
    {
        return Cache::has($this->prefix . $offerId);
    }

    public function get(string $offerId)
    {
        return Cache::get($this->prefix . $offerId);
    }

    public function put(string $offerId, $data, ?int $ttl = null): void
    {
        Cache::put($this->prefix . $offerId, $data, $ttl ?? $this->ttl);
    }

    public function forget(string $offerId): void
    {
        Cache::forget($this->prefix . $offerId);
    }
}