<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * List all users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount(['articles', 'comments', 'contributions']);

        // Role filter
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->input('status') === 'banned') {
            $query->onlyTrashed();
        }

        $users = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * Get user details.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::withTrashed()
            ->withCount(['articles', 'comments', 'contributions', 'bookmarks'])
            ->with(['preferences', 'contributions' => fn ($q) => $q->latest()->limit(10)])
            ->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        // Prevent changing own role
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Cannot change your own role.'],
            ], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:user,contributor,editor,admin,super_admin'],
        ]);

        // Only super_admin can create other super_admins
        if ($validated['role'] === 'super_admin' && $request->user()->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Only super admins can assign super admin role.'],
            ], 403);
        }

        $user->update(['role' => $validated['role']]);

        return response()->json([
            'success' => true,
            'data' => ['role' => $user->role],
            'message' => 'User role updated successfully.',
        ]);
    }

    /**
     * Update user status (ban/unban).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'User not found.'],
            ], 404);
        }

        // Prevent banning self
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Cannot ban yourself.'],
            ], 403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:ban,unban'],
        ]);

        if ($validated['action'] === 'ban') {
            $user->delete(); // Soft delete
            $user->tokens()->delete(); // Revoke all tokens
        } else {
            $user->restore();
        }

        return response()->json([
            'success' => true,
            'message' => $validated['action'] === 'ban' ? 'User banned successfully.' : 'User unbanned successfully.',
        ]);
    }
}
