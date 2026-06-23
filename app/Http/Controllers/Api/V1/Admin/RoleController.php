<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * الحصول على جميع المستخدمين مع أدوارهم
     */
    public function index(Request $request)
    {
        // فقط Admin يمكنه مشاهدة قائمة المستخدمين
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::select('id', 'name', 'email', 'phone', 'role', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * تحديث دور المستخدم
     */
    public function updateRole(Request $request, User $user)
    {
        // فقط Admin يمكنه تغيير الأدوار
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'manager', 'user'])],
        ]);

        try {
            $user->update(['role' => $validated['role']]);

            return response()->json([
                'data' => $user,
                'message' => 'Role updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث دور المستخدم باستخدام الايميل
     */
    public function updateRoleByEmail(Request $request)
    {
        // فقط Admin يمكنه تغيير الأدوار
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', Rule::in(['admin', 'manager', 'user'])],
        ]);

        try {
            $user = User::where('email', $validated['email'])->firstOrFail();
            $user->update(['role' => $validated['role']]);

            return response()->json([
                'data' => $user,
                'message' => 'Role updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found or update failed: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * الحصول على إحصائيات الأدوار
     */
    public function getRoleStats()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'managers' => User::where('role', 'manager')->count(),
            'users' => User::where('role', 'user')->count(),
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'Role statistics retrieved successfully'
        ]);
    }

    /**
     * البحث عن مستخدم بالايميل
     */
    public function searchByEmail(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])
            ->select('id', 'name', 'email', 'phone', 'role', 'created_at')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'data' => $user,
            'message' => 'User found successfully'
        ]);
    }
}
