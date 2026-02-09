<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyVendor
{
    /**
     * Handle an incoming request.
     * Allows both vendors and admins to access vendor areas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        // Allow vendors and admins to access vendor areas
        $isVendor = $user->user_type === 'vendor';
        $isAdmin = $user->is_admin || $user->user_type === 'admin';

        if (!$isVendor && !$isAdmin) {
            return redirect()->route('rizqmall.home')
                ->with('error', 'This area is for vendors only.');
        }

        return $next($request);
    }
}
