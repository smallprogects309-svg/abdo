<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin - Course Resource (Full Details)
 * بيانات الكورس الكاملة للـ admin
 */
class CourseDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'instructor_id' => $this->instructor_id,
            'instructor_name' => $this->instructor_name,
            'category_id' => $this->category_id,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'level' => $this->level,
            'cover_image' => $this->cover_image,
            'is_published' => (bool) $this->is_published,
            'total_lessons' => $this->lessons_count ?? 0,
            'total_students' => $this->enrollments_count ?? 0,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
