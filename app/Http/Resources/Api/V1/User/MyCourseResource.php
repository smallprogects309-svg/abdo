<?php

namespace App\Http\Resources\Api\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Core\LessonResource;

/**
 * User - My Course Resource  
 * الكورسات المسجل فيها المستخدم
 */
class MyCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'instructor_name' => $this->instructor_name,
            'price' => (float) $this->price,
            'cover_image' => $this->cover_image,
            'level' => $this->level,
            'progress' => $this->calculateProgress(),
            'lessons_count' => $this->lessons()->count(),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'enrolled_at' => $this->getEnrollmentDate(),
        ];
    }

    private function calculateProgress(): int
    {
        // TODO: حساب تقدم المستخدم في الكورس
        return 0;
    }

    private function getEnrollmentDate(): ?string
    {
        // TODO: الحصول على تاريخ الالتحاق
        return null;
    }
}
