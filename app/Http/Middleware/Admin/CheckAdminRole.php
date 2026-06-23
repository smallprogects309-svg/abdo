<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Admin Role Middleware
 * 
 * التحقق من أن المستخدم admin
 */
class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من أن المستخدم مسجل دخول
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401
            ], 401);
        }

        // التحقق من أن الـ role = admin
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Admin role required.',
                'status' => 403,
                'user_role' => auth()->user()->role
            ], 403);
        }

        return $next($request);
    }
}
