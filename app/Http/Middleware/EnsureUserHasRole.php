<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // Admin can access everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        $flatRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $part) {
                $flatRoles[] = trim($part);
            }
        }

        if (!in_array($userRole, $flatRoles)) {
            abort(403, 'Unauthorized access to this portal.');
        }

        if ($userRole === 'admin_assistant' && !$request->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            return redirect()->route('login')->with('error', 'Your admin assistant account has been deactivated. Please contact the court owner.');
        }

        return $next($request);
    }
}
