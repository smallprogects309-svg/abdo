<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check User Role Middleware
 * 
 * التحقق من أن المستخدم student (ليس admin)
 */
class CheckUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401
            ], 401);
        }

        // السماح فقط للمستخدمين العاديين (student)
        if (auth()->user()->role !== 'student' && auth()->user()->role !== 'user') {
            return response()->json([
                'message' => 'Access denied. User role required.',
                'status' => 403,
                'current_role' => auth()->user()->role
            ], 403);
        }

        return $next($request);
    }
}
