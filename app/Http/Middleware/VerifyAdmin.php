<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        if (!$user->is_admin && $user->user_type !== 'admin') {
            return redirect()->route('rizqmall.home')
                ->with('error', 'This area is for administrators only.');
        }

        return $next($request);
    }
}
