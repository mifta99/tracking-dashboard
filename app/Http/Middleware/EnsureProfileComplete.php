<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileComplete
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
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user needs to complete profile (puskesmas users only)
            if ($user->role_id == 1 && $user->must_change_password == 1) {
                // Allow access to profile edit routes
                if ($request->routeIs('puskesmas.profile.*')) {
                    return $next($request);
                }
                
                // Redirect to profile completion for all other routes
                return redirect()->route('puskesmas.profile.edit')
                    ->with('info', 'Silakan lengkapi profil Anda terlebih dahulu.');
            }
        }
        
        return $next($request);
    }
}