<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // System Administrator and Court Owner have unrestricted access
        if ($user->isAdmin() || $user->isOwner()) {
            return $next($request);
        }

        // If the user is an admin assistant, verify active status and module permission
        if ($user->isAdminAssistant()) {
            if (!$user->is_active) {
                auth()->logout();
                $request->session()->invalidate();
                return redirect()->route('login')->with('error', 'Your admin assistant account has been deactivated. Please contact the court owner.');
            }

            if ($user->hasModuleAccess($module)) {
                return $next($request);
            }

            // If assistant attempted to visit dashboard but only has other modules (e.g. approvals), redirect to allowed module
            if ($request->routeIs('owner.dashboard')) {
                $allowedRoute = $user->getFirstAllowedRoute();
                if ($allowedRoute !== 'owner.dashboard' && $allowedRoute !== 'home') {
                    return redirect()->route($allowedRoute)->with('info', 'Welcome! Redirected to your assigned module.');
                }
            }

            $moduleNames = [
                'schedule' => 'Viewing of Schedule & Reservations',
                'approvals' => 'Approval of Reservation',
                'courts' => 'Courts & Pricing',
                'photos' => 'Website Photos',
                'settings' => 'Payment & Center Config',
            ];

            $displayName = $moduleNames[$module] ?? ucfirst($module);

            abort(403, "Access Denied: You do not have permission to access the '{$displayName}' module. Please contact the court owner to request access.");
        }

        abort(403, 'Unauthorized portal access.');
    }
}
