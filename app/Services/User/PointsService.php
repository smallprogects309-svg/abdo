<?php

namespace App\Services\User;

use App\Models\User\User;

class PointsService
{
    // Points configuration
    const VIDEO_COMPLETED = 50;
    const QUIZ_PASSED = 100;
    const STREAK_BONUS = 10;
    const LEVEL_THRESHOLD = 500; // Points needed per level

    /**
     * Add points untuk video completion
     */
    public function addVideoCompletionPoints(User $user): void
    {
        $this->addPoints($user, self::VIDEO_COMPLETED, 'video_completed');
    }

    /**
     * Add points untuk quiz passing
     */
    public function addQuizPassPoints(User $user): void
    {
        $this->addPoints($user, self::QUIZ_PASSED, 'quiz_passed');
    }

    /**
     * Add points dengan level check
     */
    public function addPoints(User $user, int $points, string $reason = 'action'): array
    {
        $oldPoints = $user->points;
        $oldLevel = $user->level;

        // Add points
        $user->points += $points;
        $user->save();

        // Check untuk level up
        $newLevel = $this->calculateLevel($user->points);
        $leveledUp = false;

        if ($newLevel > $oldLevel) {
            $user->level = $newLevel;
            $user->save();
            $leveledUp = true;
        }

        return [
            'user_id' => $user->id,
            'points_added' => $points,
            'total_points' => $user->points,
            'previous_points' => $oldPoints,
            'level' => $user->level,
            'level_up' => $leveledUp,
            'previous_level' => $oldLevel,
            'reason' => $reason,
            'next_level_requires' => ($newLevel * self::LEVEL_THRESHOLD),
            'progress_to_next' => ($user->points % self::LEVEL_THRESHOLD),
        ];
    }

    /**
     * Calculate level from points
     */
    public function calculateLevel(int $points): int
    {
        if ($points < self::LEVEL_THRESHOLD) {
            return 1;
        }

        return intval($points / self::LEVEL_THRESHOLD) + 1;
    }

    /**
     * Get progress to next level
     */
    public function getProgressToNextLevel(User $user): array
    {
        $currentLevel = $user->level;
        $pointsForCurrentLevel = $currentLevel * self::LEVEL_THRESHOLD;
        $pointsForNextLevel = ($currentLevel + 1) * self::LEVEL_THRESHOLD;

        $progress = $user->points - $pointsForCurrentLevel;
        $required = $pointsForNextLevel - $pointsForCurrentLevel;

        return [
            'current_level' => $currentLevel,
            'next_level' => $currentLevel + 1,
            'points_in_current_level' => $progress,
            'points_required_for_next' => $required,
            'percentage' => round(($progress / $required) * 100, 2),
        ];
    }

    /**
     * Get leaderboard top N users
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $users = User::where('role', 'student')
            ->orderByDesc('points')
            ->orderByDesc('level')
            ->take($limit)
            ->get()
            ->map(function ($user, $rank) {
                return [
                    'rank' => $rank + 1,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'points' => $user->points,
                    'level' => $user->level,
                    'progress' => $this->getProgressToNextLevel($user),
                ];
            });

        return $users->toArray();
    }

    /**
     * Reset user points (for admin)
     */
    public function resetPoints(User $user): void
    {
        $user->update([
            'points' => 0,
            'level' => 1,
        ]);
    }

    /**
     * Add custom points
     */
    public function addCustomPoints(User $user, int $points, string $reason): array
    {
        return $this->addPoints($user, $points, $reason);
    }
}
