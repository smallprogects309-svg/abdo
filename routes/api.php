<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Core\BaseApiController;
use App\Http\Controllers\Api\V1\User\{CourseController, LessonController, LeaderboardController, EnrollmentController, ProgressController};
use App\Http\Controllers\Api\V1\Admin\{RoleController, CourseManagementController, LessonManagementController};

/*
|--------------------------------------------------------------------------
| API Routes - منصة التعليم (بني سويف)
|--------------------------------------------------------------------------
| جميع الـ Routes تحت /api/v1
*/

// 1. مسارات عامة (Public) - بدون auth
Route::prefix('v1')->middleware(['api'])->group(function () {
    // Auth routes (public)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Public courses - Specific routes FIRST (before {id})
    Route::get('/courses/search', [CourseController::class, 'search']);
    Route::get('/courses/popular', [CourseController::class, 'popular']);
    Route::get('/courses/trending', [CourseController::class, 'trending']);
    Route::get('/courses/export/json', [CourseController::class, 'exportJson']);
    Route::get('/courses/export/csv', [CourseController::class, 'exportCsv']);
    Route::get('/courses/search-lazy', [CourseController::class, 'searchLazy']);
    Route::get('/courses/category/{categoryId}/stats', [CourseController::class, 'categoryStats']);
    Route::get('/courses/category/{categoryId}', [CourseController::class, 'byCategory']);
    
    // Dynamic routes LAST
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);

    // لوحة الترتيب العامة
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
});

// 2. مسارات محمية (Protected - Sanctum)
Route::prefix('v1')->middleware(['api', 'auth:sanctum'])->group(function () {

    // بيانات المستخدم الحالي وتسجيل الخروج
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // لوحة الترتيب والإحصائيات
    Route::get('/leaderboard/me', [LeaderboardController::class, 'getUserRank']);
    Route::get('/user/stats', [LeaderboardController::class, 'getUserStats']);

    // الكورسات والدروس (Protected versions)
    Route::get('/user/courses', [CourseController::class, 'index']);
    Route::get('/user/courses/{course}', [CourseController::class, 'show']);
    Route::get('/user/courses/{course}/lessons', [LessonController::class, 'getByCourse']);
    Route::get('/user/lessons/{lesson}', [LessonController::class, 'show']);

    // الاشتراكات والتقدم
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::get('/enrollments', [EnrollmentController::class, 'getStudentEnrollments']);

    Route::get('/progress/{lesson}', [ProgressController::class, 'getProgress']);
    Route::post('/progress/{lesson}', [ProgressController::class, 'updateProgress']);
    Route::post('/progress/{lesson}/complete', [ProgressController::class, 'markLessonComplete']);

    // 3. مسارات الإدارة (Admin Only)
    Route::middleware('admin')->group(function () {
        // إدارة الأدوار
        Route::get('/roles/users', [RoleController::class, 'index']);
        Route::put('/roles/users/{user}', [RoleController::class, 'updateRole']);
        Route::post('/roles/update-by-email', [RoleController::class, 'updateRoleByEmail']);
        Route::get('/roles/stats', [RoleController::class, 'getRoleStats']);
        Route::post('/roles/search', [RoleController::class, 'searchByEmail']);

        // الكورسات
        // TODO: Create admin course routes in Admin\CourseController
        // Route::post('/courses', [AdminCourseController::class, 'store']);
        // Route::put('/courses/{course}', [AdminCourseController::class, 'update']);
        // Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy']);

        // Course Management Routes
        Route::prefix('/admin/courses')->group(function () {
            Route::get('/', [CourseManagementController::class, 'index']);
            Route::post('/', [CourseManagementController::class, 'store']);
            Route::get('/{id}', [CourseManagementController::class, 'show']);
            Route::put('/{id}', [CourseManagementController::class, 'update']);
            Route::delete('/{id}', [CourseManagementController::class, 'destroy']);
            Route::post('/reorder', [CourseManagementController::class, 'reorder']);
        });

        // Lesson Management Routes
        Route::prefix('/admin/lessons')->group(function () {
            Route::get('/course/{courseId}', [LessonManagementController::class, 'indexByCourse']);
            Route::post('/', [LessonManagementController::class, 'store']);
            Route::get('/{id}', [LessonManagementController::class, 'show']);
            Route::put('/{id}', [LessonManagementController::class, 'update']);
            Route::delete('/{id}', [LessonManagementController::class, 'destroy']);
            Route::post('/upload-attachments', [LessonManagementController::class, 'uploadAttachments']);
            Route::delete('/attachments/{id}', [LessonManagementController::class, 'deleteAttachment']);
            Route::post('/reorder', [LessonManagementController::class, 'reorderLessons']);
        });
    });
});
