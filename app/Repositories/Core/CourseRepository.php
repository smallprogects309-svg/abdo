<?php

namespace App\Repositories\Core;

use App\Models\Core\Course;

/**
 * Course Repository
 * محسّن مع Eager Loading منع N+1
 */
class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        $this->setModel($model);
    }

    /**
     * Get active courses
     */
    public function getActive(int $perPage = 15)
    {
        return $this->with(['instructor', 'category'])
            ->query()
            ->where('is_published', true)
            ->paginate($perPage);
    }

    /**
     * Get courses for dashboard (instructor)
     */
    public function getByInstructor(int $instructorId, int $perPage = 15)
    {
        return $this->with(['category', 'lessons'])
            ->withCount(['enrollments', 'reviews'])
            ->query()
            ->where('instructor_id', $instructorId)
            ->paginate($perPage);
    }

    /**
     * Get courses by category with filters
     */
    public function getBycategory(int $categoryId, array $filters = [], int $perPage = 15)
    {
        $query = $this->with(['instructor', 'category'])
            ->withCount(['enrollments'])
            ->query()
            ->where('category', $categoryId)
            ->where('is_published', true);

        // Apply filters
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['minPrice']) || isset($filters['maxPrice'])) {
            $minPrice = $filters['minPrice'] ?? 0;
            $maxPrice = $filters['maxPrice'] ?? PHP_INT_MAX;
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        if (isset($filters['searchQuery'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['searchQuery']}%")
                  ->orWhere('description', 'like', "%{$filters['searchQuery']}%");
            });
        }

        // Sort
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortOrder = $filters['sortOrder'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Search courses with full text
     */
    public function search(string $query, array $columns = ['*'])
    {
        return $this->with(['instructor', 'category'])
            ->withCount(['enrollments', 'reviews'])
            ->query()
            ->select($columns)
            ->where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByRaw("MATCH(title, description) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->get();
    }

    /**
     * Get popular courses
     */
    public function getPopular(int $limit = 10)
    {
        return $this->with(['instructor', 'category'])
            ->withCount(['enrollments'])
            ->query()
            ->where('is_published', true)
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending courses (recent with high enrollments)
     */
    public function getTrending(int $limit = 5)
    {
        return $this->with(['instructor', 'category'])
            ->withCount(['enrollments'])
            ->query()
            ->where('is_published', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get course with all details
     */
    public function getWithDetails(int $courseId)
    {
        return $this->with([
            'lessons' => function ($query) {
                $query->orderBy('order_position');
            },
        ])
        ->withCount(['lessons', 'enrollments'])
        ->query()
        ->findOrFail($courseId);
    }

    /**
     * Duplicate course
     */
    public function duplicate(int $courseId, array $newData)
    {
        $original = $this->find($courseId);
        
        $data = $original->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['slug']);
        
        $data = array_merge($data, $newData);
        
        return $this->create($data);
    }

    /**
     * ⚡ LAZY METHODS - للكورسات الضخمة
     */

    /**
     * Get all active courses as lazy collection
     * مثالاً: تصدير 100,000 كورس بدون استهلاك الرام
     */
    public function lazyActive()
    {
        return $this->lazyBy('status', 'active');
    }

    /**
     * Get all courses by instructor as lazy
     * للمعالجة الفورية لكورسات المعلم
     */
    public function lazyByInstructor(int $instructorId)
    {
        return $this->lazyBy('instructor_id', $instructorId);
    }

    /**
     * Get courses by category as lazy
     * للـ bulk operations على جميع كورسات فئة معينة
     */
    public function lazyByCategory(int $categoryId)
    {
        return $this->lazyBy('category_id', $categoryId);
    }

    /**
     * Export all courses efficiently
     * CSV export, reporting, analytics
     */
    public function lazyExport(array $columns = ['id', 'title', 'slug', 'price', 'status', 'created_at'])
    {
        return $this->exportLazy($columns);
    }

    /**
     * Process all courses with callback
     * مثالاً: تحديث prices لـ 1 مليون كورس
     */
    public function processEach(callable $callback, string $status = 'active')
    {
        $this->model
            ->where('status', $status)
            ->lazy($this->chunkSize)
            ->each($callback);
    }

    /**
     * Get active courses titles only
     * للـ cache warming - استخراج الأسماء بكفاءة
     */
    public function lazyActiveTitles($useId = false)
    {
        return $this->lazyPluck('title', $useId ? 'id' : null)
            ->filter(fn($title) => !empty($title));
    }

    /**
     * Filter courses by price range as lazy
     */
    public function lazyByPriceRange(float $minPrice, float $maxPrice)
    {
        return $this->lazy()
            ->filter(fn($course) => $course->price >= $minPrice && $course->price <= $maxPrice);
    }

    /**
     * Map courses to array for JSON as lazy
     */
    public function lazyAsArray()
    {
        return $this->lazyMap(fn($course) => $course->toArray());
    }
}
