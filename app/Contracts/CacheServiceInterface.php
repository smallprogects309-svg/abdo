<?php

namespace App\Contracts;

/**
 * Cache Service Contract
 * Interface لـ Cache operations
 */
interface CacheServiceInterface
{
    /**
     * Get from cache
     */
    public function get(string $key, $default = null);

    /**
     * Put in cache
     */
    public function put(string $key, $value, int $minutes = 60);

    /**
     * Forever cache
     */
    public function forever(string $key, $value);

    /**
     * Forget from cache
     */
    public function forget(string $key);

    /**
     * Flush all cache
     */
    public function flush();

    /**
     * Remember or retrieve
     */
    public function remember(string $key, int $minutes, callable $callback);
}
