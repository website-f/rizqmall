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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        // Check if user is a vendor
        if ($user->user_type !== 'vendor') {
            return redirect()->route('rizqmall.home')
                ->with('error', 'This area is for vendors only.');
        }

        return $next($request);
    }
}
