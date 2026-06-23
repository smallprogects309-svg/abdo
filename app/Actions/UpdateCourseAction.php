<?php

namespace App\Actions;

use App\DTOs\CourseDTO;
use App\Models\Core\Course;
use App\Repositories\Core\CourseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Update Course Action
 * Action لتحديث الكورس
 */
class UpdateCourseAction extends BaseAction
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Execute the action
     */
    public function execute(int $courseId, CourseDTO $dto): Course
    {
        try {
            return DB::transaction(function () use ($courseId, $dto) {
                // Get original course
                $course = $this->courseRepository->find($courseId);

                // Prepare data (only changed fields)
                $data = $this->prepareData($dto);

                // Update course
                $updated = $this->courseRepository->update($courseId, $data);

                // Cache invalidation
                $this->invalidateCache('courses:*');
                $this->invalidateCache("course:{$courseId}:*");

                // Log
                $this->log('course_updated', [
                    'course_id' => $courseId,
                    'changes' => $data,
                ]);

                return $updated;
            });
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Prepare data for update
     */
    private function prepareData(CourseDTO $dto): array
    {
        $data = [];

        if ($dto->title) {
            $data['title'] = $dto->title;
        }
        if ($dto->description) {
            $data['description'] = $dto->description;
        }
        if ($dto->content) {
            $data['content'] = $dto->content;
        }
        if ($dto->price !== null) {
            $data['price'] = $dto->price;
        }
        if ($dto->discount_price !== null) {
            $data['discount_price'] = $dto->discount_price;
        }
        if ($dto->duration) {
            $data['duration'] = $dto->duration;
        }
        if ($dto->level) {
            $data['level'] = $dto->level;
        }
        if ($dto->status) {
            $data['status'] = $dto->status;
        }
        if ($dto->image) {
            $data['image'] = $dto->image;
        }
        if ($dto->prerequisites) {
            $data['prerequisites'] = json_encode($dto->prerequisites);
        }
        if ($dto->learning_outcomes) {
            $data['learning_outcomes'] = json_encode($dto->learning_outcomes);
        }

        return $data;
    }
}
