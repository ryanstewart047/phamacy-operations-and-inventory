<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $request->expectsJson()) {
            return $next($request);
        }

        if ($user->isBlocked()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => __('Your account has been blocked. Please contact an administrator.'),
            ]);
        }

        $mustReset = $user->mustChangePassword();
        $needsPhoto = empty($user->profile_photo_path);

        $isProfileRoute = $request->routeIs('profile.*') || $request->routeIs('logout');

        if (($mustReset || $needsPhoto) && ! $isProfileRoute) {
            return redirect()->route('profile.edit')->with('mustCompleteProfile', true);
        }

        return $next($request);
    }
}
