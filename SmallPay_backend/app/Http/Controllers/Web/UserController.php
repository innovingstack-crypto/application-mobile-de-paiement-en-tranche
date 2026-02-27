<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index()
    {
        $query = User::query();

        $q = request()->string('q')->toString();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('role', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $users = $query->latest()->paginate(50)->withQueryString();
        return view('Admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('Admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,admin,super_admin',
            'status' => 'nullable|in:active,blocked,suspended',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = $validated['status'] ?? 'active';

        $user = User::create($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        $orders = $user->orders()->latest()->paginate(10);
        return view('Admin.users.show', compact('user', 'orders'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        return view('Admin.users.edit', compact('user'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'status' => 'required|in:active,blocked,suspended',
            'role' => 'required|in:user,admin,super_admin',
            'change_password' => 'nullable|boolean',
            'new_password' => 'nullable|string|min:6|required_if:change_password,1',
            'new_password_confirmation' => 'nullable|string|min:6|required_if:change_password,1|same:new_password',
        ]);

        // Gérer le changement de mot de passe seulement si demandé
        if ($request->boolean('change_password') && !empty($request->new_password)) {
            $validated['password'] = Hash::make($request->new_password);
        } else {
            // Ne pas inclure le mot de passe dans la validation si pas demandé
            unset($validated['new_password']);
            unset($validated['new_password_confirmation']);
            unset($validated['change_password']);
        }

        $user->update($validated);

        $message = $request->boolean('change_password') && !empty($request->new_password)
            ? 'Utilisateur mis à jour avec succès. Le mot de passe a été changé.'
            : 'Utilisateur mis à jour avec succès.';

        return redirect()->route('admin.users.show', $user)
            ->with('success', $message);
    }

    /**
     * Bloquer un utilisateur
     */
    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);
        return redirect()->back()->with('success', 'User blocked successfully');
    }

    /**
     * Débloquer un utilisateur
     */
    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);
        return redirect()->back()->with('success', 'User unblocked successfully');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }
}