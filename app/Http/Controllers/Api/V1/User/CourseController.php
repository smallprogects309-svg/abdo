<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Resources\Api\V1\Core\CourseCollection;
use App\Http\Resources\Api\V1\Core\CourseResource;
use App\Models\Core\Course;
use App\Repositories\Core\CourseRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Course Controller - User View Only
 * 
 * دوال العرض فقط للمستخدمين العاديين
 * لا توجد حقوق إدارية (Create, Update, Delete)
 */
class CourseController extends \App\Http\Controllers\Controller
{
    use ApiResponse;

    private CourseRepository $repository;

    public function __construct(CourseRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all courses (public)
     * GET /api/courses
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $perPage = min($request->query('per_page', 15), 100);
            $cacheKey = "courses:page:{$page}:per-page:{$perPage}";

            $courses = Cache::tags(['courses'])->remember(
                $cacheKey,
                now()->addHours(1),
                fn() => $this->repository->getActive($perPage)
            );

            return $this->paginated(
                new CourseCollection($courses),
                'Courses retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get single course
     * GET /api/courses/{id}
     */
    public function show(int $id)
    {
        try {
            $cacheKey = "course:{$id}:details";
            $course = Cache::tags(["course:{$id}"])->remember(
                $cacheKey,
                now()->addHours(2),
                fn() => $this->repository->getWithDetails($id)
            );

            return $this->success(
                new CourseResource($course),
                'Course retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->notFound('Course not found');
        }
    }

    /**
     * Search courses
     * GET /api/courses/search?q=laravel
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            if (strlen($query) < 2) {
                return $this->error('Search query must be at least 2 characters', 422);
            }

            $results = $this->repository->search($query);

            return $this->success(
                CourseResource::collection($results),
                'Search results'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get courses by category
     * GET /api/courses/category/{categoryId}
     */
    public function byCategory(int $categoryId, Request $request)
    {
        try {
            $filters = $request->only(['level', 'minPrice', 'maxPrice', 'searchQuery', 'sortBy', 'sortOrder']);
            $perPage = min($request->query('per_page', 15), 100);

            $courses = $this->repository->getByCategory($categoryId, $filters, $perPage);

            return $this->paginated(
                new CourseCollection($courses),
                'Courses retrieved'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get popular courses
     * GET /api/courses/popular
     */
    public function popular()
    {
        try {
            $cacheKey = 'courses:popular';
            $courses = Cache::tags(['courses'])->remember(
                $cacheKey,
                now()->addHours(6),
                fn() => $this->repository->getPopular(10)
            );

            return $this->success(
                CourseResource::collection($courses),
                'Popular courses'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get trending courses
     * GET /api/courses/trending
     */
    public function trending()
    {
        try {
            $cacheKey = 'courses:trending';
            $courses = Cache::tags(['courses'])->remember(
                $cacheKey,
                now()->addMinutes(30),
                fn() => $this->repository->getTrending(5)
            );

            return $this->success(
                CourseResource::collection($courses),
                'Trending courses'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Export courses as JSON (lazy loading)
     * GET /api/courses/export/json
     */
    public function exportJson()
    {
        try {
            $courses = $this->repository->lazyExport();
            $data = $courses->toArray();

            return response()->json([
                'success' => true,
                'count' => count($data),
                'data' => $data,
                'message' => 'Courses exported successfully'
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Export courses as CSV (lazy loading)
     * GET /api/courses/export/csv
     */
    public function exportCsv()
    {
        $filename = "courses_" . now()->format('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Title', 'Slug', 'Price', 'Status', 'Created At']);

            $this->repository->lazyExport()->each(function ($course) use ($out) {
                fputcsv($out, [
                    $course->id,
                    $course->title,
                    $course->slug,
                    $course->price,
                    $course->status,
                    $course->created_at
                ]);
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename"
        ]);
    }

    /**
     * Search with lazy (for big data)
     * GET /api/courses/search-lazy?q=laravel
     */
    public function searchLazy(Request $request)
    {
        try {
            $query = $request->input('q', '');
            
            if (strlen($query) < 2) {
                return $this->error('Search query must be at least 2 characters', 422);
            }

            $results = $this->repository->lazy(['id', 'title', 'slug'])
                ->filter(fn($course) => 
                    stripos($course->title, $query) !== false
                )
                ->take(50);

            return $this->success(
                ['count' => $results->count(), 'results' => $results->toArray()],
                'Search results'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Get category stats with lazy
     * GET /api/courses/category/{categoryId}/stats
     */
    public function categoryStats($categoryId)
    {
        try {
            $courses = $this->repository->lazyByCategory($categoryId);

            $stats = [
                'total' => $courses->count(),
                'avg_price' => (int)$courses->pluck('price')->avg(),
                'min_price' => $courses->pluck('price')->min(),
                'max_price' => $courses->pluck('price')->max(),
            ];

            return $this->success($stats, 'Category statistics');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}
