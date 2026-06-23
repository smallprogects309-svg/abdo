<?php

namespace App\Traits;

/**
 * API Response Trait
 * موحد الردود بين Web و Mobile لضمان consistency
 */
trait ApiResponse
{
    /**
     * Success Response
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @param array $extra
     */
    protected function success($data = null, $message = 'Success', $code = 200, $extra = [])
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            ...$extra,
        ], $code);
    }

    /**
     * Error Response
     * @param string $message
     * @param int $code
     * @param array|null $errors
     */
    protected function error($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Paginated Response
     * @param object $paginated
     * @param string $message
     */
    protected function paginated($paginated, $message = 'Data retrieved successfully')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ], 200);
    }

    /**
     * Created Response
     */
    protected function created($data, $message = 'Resource created successfully')
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Accepted Response (for async operations)
     */
    protected function accepted($message = 'Request accepted for processing')
    {
        return $this->success(null, $message, 202);
    }

    /**
     * No Content Response
     */
    protected function noContent($message = 'No content')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], 204);
    }

    /**
     * Validation Error Response
     */
    protected function validationError($errors, $message = 'Validation failed')
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Unauthorized Response
     */
    protected function unauthorized($message = 'Unauthorized')
    {
        return $this->error($message, 401);
    }

    /**
     * Forbidden Response
     */
    protected function forbidden($message = 'Forbidden')
    {
        return $this->error($message, 403);
    }

    /**
     * Not Found Response
     */
    protected function notFound($message = 'Resource not found')
    {
        return $this->error($message, 404);
    }

    /**
     * Conflict Response
     */
    protected function conflict($message = 'Conflict', $errors = null)
    {
        return $this->error($message, 409, $errors);
    }

    /**
     * Unprocessable Entity Response
     */
    protected function unprocessable($message = 'Unprocessable entity', $errors = null)
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Server Error Response
     */
    protected function serverError($message = 'Server error')
    {
        return $this->error($message, 500);
    }
}
