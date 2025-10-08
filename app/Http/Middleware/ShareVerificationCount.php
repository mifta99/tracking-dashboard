<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\VerificationRequest\VerificationRequestController;

class ShareVerificationCount
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
        try {
            $verificationCount = VerificationRequestController::getVerificationRequestCount();
            View::share('globalVerificationCount', $verificationCount);

            
            
            // Also update the config for AdminLTE if possible
            if (config('adminlte.menu')) {
                $menu = config('adminlte.menu');
                foreach ($menu as $key => &$menuItem) {
                    if (isset($menuItem['text']) && $menuItem['text'] === 'Permintaan Verifikasi') {
                        $menuItem['label'] = $verificationCount;
                        break;
                    }
                }
                config(['adminlte.menu' => $menu]);
            }
        } catch (\Exception $e) {
            // Fail silently if database is not available
            View::share('globalVerificationCount', 0);
        }
        
        return $next($request);
    }
}
