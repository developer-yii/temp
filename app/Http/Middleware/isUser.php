<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class isUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        /** @var User|null $user */
        if (!$user) {
            return redirect()->route('login');
        }

        // Allow users and admins to access user panel (not super admin)
        if ($user->canAccessUserPanel()) {
            return $next($request);
        }

        // Super admin goes to admin panel
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.home');
        }

        return redirect()->route('login');
    }
}
