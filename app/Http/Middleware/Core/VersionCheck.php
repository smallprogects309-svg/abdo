<?php

namespace App\Http\Middleware\Core;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Version Check Middleware
 * 
 * التحقق من الـ API version في الـ URL
 */
class VersionCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        
        // التحقق من أن الـ path يبدأ بـ api/v
        if (!preg_match('/^api\/v[1-9]/', $path)) {
            return response()->json([
                'message' => 'Invalid API version. Use /api/v1 or /api/v2',
                'valid_versions' => ['v1', 'v2']
            ], 400);
        }

        return $next($request);
    }
}
