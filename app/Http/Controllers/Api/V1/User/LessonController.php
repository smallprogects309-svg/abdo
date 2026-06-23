<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Core\LessonResource;
use App\Models\Core\Course;
use App\Models\Core\Lesson;

/**
 * Lesson Controller - User View Only
 * 
 * دوال العرض فقط للمستخدمين العاديين
 * لا توجد حقوق إدارية (Create, Update, Delete)
 */
class LessonController extends Controller
{
    /**
     * Get lessons by course
     * GET /api/courses/{course}/lessons
     */
    public function getByCourse(Course $course)
    {
        $lessons = $course->lessons()
            ->select('id', 'course_id', 'title', 'description', 'video_url', 'duration_minutes', 'order_position')
            ->orderBy('order_position')
            ->get();

        return response()->json([
            'success' => true,
            'data' => LessonResource::collection($lessons),
            'message' => 'تم جلب الدروس بنجاح',
        ]);
    }

    /**
     * Get single lesson
     * GET /api/lessons/{lesson}
     */
    public function show(Lesson $lesson)
    {
        return response()->json([
            'success' => true,
            'data' => new LessonResource($lesson),
            'message' => 'تم جلب الدرس بنجاح',
        ]);
    }
}
