<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;

/**
 * Base Action
 * كل الـ Actions ترث من هذا الـ Class
 */
abstract class BaseAction
{
    /**
     * Invalidate cache
     */
    protected function invalidateCache(string $pattern): void
    {
        $keys = Cache::store('redis')->connection()->command('KEYS', [$pattern]);
        
        if (!empty($keys)) {
            Cache::store('redis')->connection()->command('DEL', $keys);
        }
    }

    /**
     * Log action
     */
    protected function log(string $action, array $data): void
    {
        \Log::info("Action: {$action}", $data);
    }

    /**
     * Handle exceptions
     */
    protected function handleException(\Exception $e)
    {
        \Log::error('Action failed: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);

        throw $e;
    }
}
