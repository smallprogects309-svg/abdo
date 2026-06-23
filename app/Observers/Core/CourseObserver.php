<?php

namespace App\Observers\Core;

use App\Models\Core\Course;
use Illuminate\Support\Facades\Cache;

/**
 * Course Observer
 * لمراقبة أحداث قاعدة البيانات وتحديث الـ Cache
 */
class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        // Invalidate relevant caches
        $this->invalidateCaches($course);

        // Sync to search index (if using Elasticsearch, Meilisearch, etc)
        // $this->syncToSearchIndex($course);

        // Send notifications
        // event(new CourseCreated($course));
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        // Invalidate relevant caches
        $this->invalidateCaches($course);

        // Clear query cache
        Cache::forget("course:{$course->id}");
        Cache::forget("course:{$course->slug}");

        // Update search index
        // $this->syncToSearchIndex($course);

        // Send notifications
        // event(new CourseUpdated($course));
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        // Invalidate relevant caches
        $this->invalidateCaches($course);

        // Clear course-specific caches
        Cache::forget("course:{$course->id}");
        Cache::forget("course:{$course->slug}");
        Cache::forget("course:{$course->id}:details");

        // Delete from search index
        // $this->removeFromSearchIndex($course);

        // Send notifications
        // event(new CourseDeleted($course));
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        $this->invalidateCaches($course);
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        $this->invalidateCaches($course);
        
        Cache::forget("course:{$course->id}");
        Cache::forget("course:{$course->slug}");
    }

    /**
     * Invalidate related caches
     */
    private function invalidateCaches(Course $course): void
    {
        // Invalidate collection caches
        Cache::tags(['courses'])->flush();

        // Invalidate instructor courses
        if ($course->instructor_id) {
            Cache::tags(["instructor:{$course->instructor_id}"])->flush();
        }

        // Invalidate category courses
        if ($course->category_id) {
            Cache::tags(["category:{$course->category_id}"])->flush();
        }

        // Invalidate homepage cache
        Cache::forget('homepage:popular_courses');
        Cache::forget('homepage:trending_courses');

        // Invalidate featured courses
        if ($course->status === 'active') {
            Cache::forget('courses:featured');
        }
    }
}
