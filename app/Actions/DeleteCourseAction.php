<?php

namespace App\Actions;

use App\Models\Core\Course;
use App\Repositories\Core\CourseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Delete Course Action
 */
class DeleteCourseAction extends BaseAction
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Execute the action
     */
    public function execute(int $courseId): bool
    {
        try {
            return DB::transaction(function () use ($courseId) {
                // Delete course
                $deleted = $this->courseRepository->delete($courseId);

                // Cache invalidation
                $this->invalidateCache('courses:*');
                $this->invalidateCache("course:{$courseId}:*");

                // Log
                $this->log('course_deleted', [
                    'course_id' => $courseId,
                ]);

                return $deleted;
            });
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
