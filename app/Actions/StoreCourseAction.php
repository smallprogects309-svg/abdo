<?php

namespace App\Actions;

use App\DTOs\CourseDTO;
use App\Models\Core\Course;
use App\Repositories\Core\CourseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Store Course Action
 * Action واحدة لإنشاء كورس (Single Responsibility)
 */
class StoreCourseAction extends BaseAction
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Execute the action
     */
    public function execute(CourseDTO $dto): Course
    {
        try {
            return DB::transaction(function () use ($dto) {
                // Prepare data
                $data = $this->prepareData($dto);

                // Create course
                $course = $this->courseRepository->create($data);

                // Cache invalidation
                $this->invalidateCache('courses:*');

                // Log
                $this->log('course_created', [
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'instructor_id' => $course->instructor_id,
                ]);

                return $course;
            });
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Prepare data from DTO
     */
    private function prepareData(CourseDTO $dto): array
    {
        return [
            'title' => $dto->title,
            'slug' => Str::slug($dto->title) . '-' . uniqid(),
            'description' => $dto->description,
            'content' => $dto->content,
            'instructor_id' => $dto->instructor_id,
            'category_id' => $dto->category_id,
            'price' => $dto->price,
            'discount_price' => $dto->discount_price,
            'duration' => $dto->duration,
            'level' => $dto->level,
            'image' => $dto->image,
            'status' => $dto->status ?? 'draft',
            'prerequisites' => $dto->prerequisites ? json_encode($dto->prerequisites) : null,
            'learning_outcomes' => $dto->learning_outcomes ? json_encode($dto->learning_outcomes) : null,
        ];
    }
}
