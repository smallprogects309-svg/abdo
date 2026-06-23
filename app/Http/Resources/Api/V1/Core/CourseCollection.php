<?php

namespace App\Http\Resources\Api\V1\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Course Collection Resource
 * مجموعة من الكورسات مع Pagination
 */
class CourseCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => CourseResource::collection($this->collection),
        ];
    }

    /**
     * Add pagination meta data
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'pagination' => [
                    'current_page' => $this->currentPage(),
                    'last_page' => $this->lastPage(),
                    'from' => $this->firstItem(),
                    'to' => $this->lastItem(),
                    'per_page' => $this->perPage(),
                    'total' => $this->total(),
                ],
            ],
        ];
    }
}
