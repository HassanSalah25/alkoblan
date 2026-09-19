<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Ensure the request is authenticated as an active admin-type user.
     *
     * This middleware intentionally performs its own authentication check
     * (instead of relying on the generic "auth" middleware) so that guests
     * are sent to admin.login rather than the storefront's own "login" route
     * — the public/customer stream registers a route literally named
     * "login" in routes/web.php, which is what Laravel's default "auth"
     * middleware falls back to when redirecting guests. Using
     * redirect()->guest() here preserves the "redirect back after login"
     * (intended URL) behavior that the "auth" middleware would otherwise
     * have provided.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('admin.login'));
        }

        $user = Auth::user();

        if ($user->type !== 'admin' || $user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'You are not authorized to access the admin panel.',
            ]);
        }

        return $next($request);
    }
}
