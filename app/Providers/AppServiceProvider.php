<?php

namespace App\Providers;

use App\Models\User\StudentProgress;
use App\Models\Core\Course;
use App\Observers\User\StudentProgressObserver;
use App\Observers\Core\CourseObserver;
use App\Services\User\PointsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register PointsService as singleton
        $this->app->singleton(PointsService::class, function () {
            return new PointsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Course::observe(CourseObserver::class);
        StudentProgress::observe(StudentProgressObserver::class);
    }
}
