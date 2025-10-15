<?php 

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSession;

class ValidateSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionId = session('session_id');

        if (!$sessionId) {
            return $next($request);
        }

        $userSession = UserSession::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->first();

        if (!$userSession) {
            session()->forget(['session_id', 'subscription_user_id', 'user_type']);
            Auth::logout();
            
            return redirect()->route('auth.redirect')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // Update last activity
        $userSession->update(['last_activity' => now()]);

        return $next($request);
    }
}