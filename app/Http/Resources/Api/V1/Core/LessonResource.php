<?php

namespace App\Http\Resources\Api\V1\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lesson Resource
 * تنسيق JSON للدروس
 */
class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'video_url' => $this->video_url,
            'pdf_url' => $this->pdf_url,
            'order_position' => $this->order_position,
            'duration_minutes' => $this->duration_minutes,
            'created_at' => $this->created_at,
        ];
    }
}
