<?php

namespace App\Http\Resources\Api\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User - Enrollment Resource
 * بيانات الالتحاق بالكورس
 */
class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'status' => $this->status ?? 'active',
            'progress' => $this->progress ?? 0,
            'enrolled_at' => $this->created_at,
            'completed_at' => $this->completed_at?->toDateTimeString(),
        ];
    }
}
