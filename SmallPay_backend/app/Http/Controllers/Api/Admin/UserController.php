<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /api/admin/users
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $role = $request->query('role');
        $status = $request->query('status');

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when($role, fn($query) => $query->where('role', $role))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * GET /api/admin/users/{user}
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * PUT /api/admin/users/{user}
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20|unique:users,phone,' . $user->id,
            'role' => 'sometimes|required|in:user,admin,super_admin',
            'status' => 'sometimes|required|in:active,blocked,suspended',
        ]);

        $user->fill($data);
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * POST /api/admin/users/{user}/block
     */
    public function block(User $user)
    {
        $user->status = 'blocked';
        $user->save();

        return response()->json(['success' => true, 'data' => $user]);
    }

    /**
     * POST /api/admin/users/{user}/unblock
     */
    public function unblock(User $user)
    {
        $user->status = 'active';
        $user->save();

        return response()->json(['success' => true, 'data' => $user]);
    }
}
