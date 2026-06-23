<?php

namespace App\Http\Resources\Api\V1\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Core\LessonResource;

/**
 * Course Resource
 * تنسيق JSON للكورسات
 */
class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price ? (float) $this->discount_price : null,
            'final_price' => $this->getFinalPrice(),
            'discount_percentage' => $this->getDiscountPercentage(),
            'duration' => $this->duration,
            'level' => $this->level,
            'status' => $this->status,
            'image' => $this->cover_image ?? $this->image,
            'instructor_name' => $this->instructor_name,
            'rating' => (float) ($this->rating ?? 0),
            'total_lessons' => $this->lessons_count ?? $this->lessons?->count() ?? 0,
            'enrollment_count' => $this->enrollments_count ?? $this->enrollments?->count() ?? 0,
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get final price after discount
     */
    private function getFinalPrice(): float
    {
        if (!$this->discount_price) {
            return (float) $this->price;
        }
        return (float) $this->discount_price;
    }

    /**
     * Get discount percentage
     */
    private function getDiscountPercentage(): ?float
    {
        if (!$this->discount_price) {
            return null;
        }
        $discount = $this->price - $this->discount_price;
        return round(($discount / $this->price) * 100, 2);
    }
}
