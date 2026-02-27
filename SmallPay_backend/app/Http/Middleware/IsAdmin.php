<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
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

        return redirect('login')->with('error', 'You dont have admin access');
    }
}
