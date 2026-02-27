<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response(
                [
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ],
                403
            )->header('Content-Type', 'application/json');
        }

        return redirect()->route('dashboard')->with('error', 'Accès non autorisé.');
    }
}
