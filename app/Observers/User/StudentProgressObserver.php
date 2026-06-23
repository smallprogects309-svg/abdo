<?php

namespace App\Observers\User;

use App\Models\User\StudentProgress;
use App\Services\User\PointsService;

class StudentProgressObserver
{
    public function __construct(private PointsService $pointsService) {}

    /**
     * Handle the StudentProgress "updated" event.
     */
    public function updated(StudentProgress $progress): void
    {
        // Check if watched_percentage just reached 100%
        if ($progress->watched_percentage == 100 && $progress->wasChanged('watched_percentage')) {
            $previousPercentage = $progress->getOriginal('watched_percentage');

            // Only add points if it wasn't 100% before
            if ($previousPercentage < 100) {
                $this->pointsService->addVideoCompletionPoints($progress->user);
            }
        }
    }

    /**
     * Handle the StudentProgress "created" event.
     */
    public function created(StudentProgress $progress): void
    {
        // If created with 100%, award points
        if ($progress->watched_percentage == 100) {
            $this->pointsService->addVideoCompletionPoints($progress->user);
        }
    }
}
