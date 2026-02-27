<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Afficher le formulaire de login
     */
    public function loginView()
    {
        return view('Admin.auth.login');
    }

    /**
     * Traiter le login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        try {
            // Utiliser la méthode adminLogin du service
            $result = $this->authService->adminLogin($validated['email'], $validated['password']);

            // Si le login réussit, on authentifie l'utilisateur
            if ($result && isset($result['user'])) {
                Auth::login($result['user']);
                return redirect()->route('dashboard')->with('success', 'Connecté avec succès');
            }

            return redirect()->back()->with('error', 'Identifiants invalides');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Déconnecté avec succès');
    }
}
