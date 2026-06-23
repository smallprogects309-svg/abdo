<?php

namespace App\Http\Middleware\Core;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * Core API Middleware
 * 
 * تطبيق الـ Security Headers, CORS, Logging, Rate Limiting
 */
class ApiMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // إجباري الـ JSON Response
        $request->headers->set('Accept', 'application/json');

        // معالجة HEAD requests من OPTIONS
        if ($request->isMethod('HEAD')) {
            $request->setMethod('GET');
        }

        $response = $next($request);
        
        // Security Headers
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->header('Content-Security-Policy', "default-src 'self'");

        // Logging
        $this->logRequest($request);

        // Rate Limiting
        $this->checkRateLimit($request);

        return $response;
    }

    private function logRequest(Request $request): void
    {
        Log::info('API Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    private function checkRateLimit(Request $request): void
    {
        $ip = $request->ip();
        $key = "rate_limit:{$ip}";
        $maxAttempts = 60;
        $decayMinutes = 1;

        $cache = cache();
        $attempts = $cache->get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            Log::warning("Rate limit exceeded for IP: {$ip}");
            return;
        }

        $cache->put($key, $attempts + 1, now()->addMinutes($decayMinutes));
    }
}
