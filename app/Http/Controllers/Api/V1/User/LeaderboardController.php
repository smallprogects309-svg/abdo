<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\User\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Leaderboard Controller - User View Only
 * 
 * عرض لوحة الترتيب والإحصائيات الشخصية فقط
 * لا توجد حقوق إدارية
 */
class LeaderboardController extends Controller
{
    public function __construct(private PointsService $pointsService) {}

    /**
     * Get top users by points
     * GET /api/leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min($request->query('limit', 10), 100); // Max 100

        $leaderboard = $this->pointsService->getLeaderboard($limit);

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
            'total' => count($leaderboard),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get current user ranking
     * GET /api/leaderboard/me
     */
    public function getUserRank(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $leaderboard = $this->pointsService->getLeaderboard(1000); // Get all for ranking

        $userRank = collect($leaderboard)
            ->firstWhere('user_id', $user->id);

        if (!$userRank) {
            return response()->json([
                'success' => false,
                'message' => 'User not in leaderboard',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $userRank,
            'message' => 'Your ranking',
        ]);
    }

    /**
     * Get user stats
     * GET /api/user/stats
     */
    public function getUserStats(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $progressService = new PointsService();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'points' => $user->points,
                'level' => $user->level,
                'progress_to_next_level' => $progressService->getProgressToNextLevel($user),
                'total_users_above' => \App\Models\User::where('points', '>', $user->points)->count(),
            ],
        ]);
    }
}
