<?php

namespace App\Services\Core;

/**
 * Base Service Class
 * 
 * كل الـ Services يرث من هذا الـ Class
 * يحتوي على الوظائف المشتركة والمساعدات
 */
abstract class BaseService
{
    /**
     * Repository instance
     */
    protected $repository;

    /**
     * Setup Repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Get Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Format success response
     */
    protected function formatResponse($success, $data = null, $message = '', $errors = null)
    {
        return [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
