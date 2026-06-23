<?php

namespace App\Http\Controllers\Api\V1\Core;

use App\Http\Controllers\Controller;

/**
 * Base Controller للـ API
 * 
 * جميع API Controllers يجب أن ترث من هذا الـ Controller
 * يضم الوظائف المشتركة والمساعدات
 */
class BaseApiController extends Controller
{
    /**
     * Response Success
     */
    public function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Response Error
     */
    public function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Response Paginated Data
     */
    public function paginatedResponse($paginated, $message = 'Data retrieved successfully')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ], 200);
    }
}
