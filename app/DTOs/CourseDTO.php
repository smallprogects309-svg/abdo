<?php

namespace App\DTOs;

/**
 * Course DTO
 * لنقل بيانات الكورس بشكل آمن بين الطبقات
 */
class CourseDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $title = null;
    public ?string $slug = null;
    public ?string $description = null;
    public ?string $content = null;
    public ?int $instructor_id = null;
    public ?int $category_id = null;
    public ?float $price = null;
    public ?float $discount_price = null;
    public ?int $duration = null; // بالدقائق
    public ?string $level = null; // beginner, intermediate, advanced
    public ?int $total_lessons = null;
    public ?string $image = null;
    public ?string $status = null; // active, inactive, draft
    public ?array $prerequisites = [];
    public ?array $learning_outcomes = [];
    public ?float $rating = null;
    public ?int $enrollment_count = null;

    /**
     * Validation rules
     */
    public static function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'instructor_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'duration' => 'required|integer|min:1',
            'level' => 'required|in:beginner,intermediate,advanced',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive,draft',
            'prerequisites' => 'nullable|array',
            'learning_outcomes' => 'nullable|array',
        ];
    }

    /**
     * Get only changed fields (for updates)
     */
    public function onlyChanged(CourseDTO $original): array
    {
        $changed = [];
        foreach ($this->toArray() as $key => $value) {
            if ($value !== ($original->$key ?? null)) {
                $changed[$key] = $value;
            }
        }
        return $changed;
    }
}
