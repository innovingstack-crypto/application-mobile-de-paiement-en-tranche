<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminIsAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifie si l'utilisateur est authentifié avec la garde 'admin'
        if (! Auth::guard('admin')->check()) {
            // S'il n'est pas connecté, on le redirige vers la page de login
            return redirect()->route('login'); // Utilisez le nom de votre route de login
        }

        // Vérifier si le compte est bloqué
        $user = Auth::guard('admin')->user();
        if ($user && $user->status === 'blocked') {
            Auth::guard('admin')->logout();
            return redirect('/login')->with('error', 'Votre compte a été bloqué. Veuillez contacter le support.');
        }

        return $next($request);
    }
}
