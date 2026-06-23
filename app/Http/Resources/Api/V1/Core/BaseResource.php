<?php

namespace App\Http\Resources\Api\V1\Core;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base Resource
 * 
 * جميع Resources ترث من هذا الـ Class
 */
abstract class BaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    abstract public function toArray($request): array;

    /**
     * Include meta information if needed
     */
    public function with($request): array
    {
        return [];
    }

    /**
     * Set HTTP status code
     */
    public function withResponse($request, $response): void
    {
        $response->setStatusCode(200);
    }
}
