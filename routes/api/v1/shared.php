<?php

use App\Http\Controllers\Api\V1\User\CourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Shared Routes (Both Web & Mobile)
|--------------------------------------------------------------------------
| Base URL: /api/v1/ (prefix applied in main routes/api.php)
*/

Route::middleware(['api'])->group(function () {
    // Course - Public endpoints (View Only)
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/courses/search', [CourseController::class, 'search']);
    Route::get('/courses/category/{categoryId}', [CourseController::class, 'byCategory']);
    Route::get('/courses/popular', [CourseController::class, 'popular']);
    Route::get('/courses/trending', [CourseController::class, 'trending']);

    // ⚡ Lazy Loading Endpoints - للبيانات الضخمة
    Route::get('/courses/export/json', [CourseController::class, 'exportJson']);
    Route::get('/courses/export/csv', [CourseController::class, 'exportCsv']);
    Route::get('/courses/search-lazy', [CourseController::class, 'searchLazy']);
    Route::get('/courses/category/{categoryId}/stats', [CourseController::class, 'categoryStats']);
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    // Course - Protected endpoints (View Only)
    // Admin routes will be created in Admin\CourseController
});
