<?php

namespace App\Http\Middleware\Core;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS Middleware
 * 
 * تحديد الـ domains المسموحة والـ methods
 */
class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // الـ domains المسموحة
        $allowedOrigins = [
            'http://localhost:5173',
            'http://localhost:5174',
            'http://localhost:3000',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:5174',
        ];
        
        $origin = $request->header('Origin');
        
        // معالجة OPTIONS requests (preflight)
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflight($request, $origin, $allowedOrigins);
        }

        $response = $next($request);

        // إضافة CORS headers للـ response
        if (in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '3600');
        }

        return $response;
    }

    private function handlePreflight(Request $request, ?string $origin, array $allowedOrigins): Response
    {
        $response = response('', 200);
        
        if (in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '3600');
        }

        return $response;
    }
}
