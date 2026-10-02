<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // If user is not authenticated, redirect to login
        if (!$request->user()) {
            return redirect('/login');
        }

        // Check if user has one of the required roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // User doesn't have the required role. For JSON/API clients return a
        // 403; for normal page visits, send them back to the dashboard with a
        // message rather than a bare error page.
        if ($request->expectsJson()) {
            abort(403, 'Unauthorized - Insufficient role permissions');
        }

        return redirect()->route('dashboard')
            ->with('error', 'You do not have access to that module.');
    }
}
