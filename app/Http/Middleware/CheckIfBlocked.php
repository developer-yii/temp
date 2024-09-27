<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIfBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is logged in and if they are blocked
        if (Auth::check() && Auth::user()->is_block == 1) {
            // Log the user out and redirect them to the login page with a message

            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'blocked' => true,
                    'message' => 'Your account is blocked'
                ], 403);
            }

            Auth::logout();
            return redirect('login')->withErrors(['approve' => 'Your account is blocked.']);
        }

        return $next($request);

    }
}
