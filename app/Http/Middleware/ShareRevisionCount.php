<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\DaftarRevisiController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\Incident\IncidentController;

class ShareRevisionCount
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
            $revisionCount = DaftarRevisiController::getTotalRevisionCount();
            $complaintCount = KeluhanController::getTotalComplaintCount();
            $incidentCount = IncidentController::getTotalIncidentCount();
            
            View::share('globalRevisionCount', $revisionCount);
            View::share('globalComplaintCount', $complaintCount);
            View::share('globalIncidentCount', $incidentCount);
            
            // Also update the config for AdminLTE if possible
            if (config('adminlte.menu')) {
                $menu = config('adminlte.menu');
                foreach ($menu as $key => &$menuItem) {
                    if (isset($menuItem['text']) && $menuItem['text'] === 'Daftar Revisi') {
                        $menuItem['label'] = $revisionCount;
                    } elseif (isset($menuItem['text']) && $menuItem['text'] === 'Pelaporan Keluhan') {
                        $menuItem['label'] = $complaintCount;
                    } elseif (isset($menuItem['text']) && $menuItem['text'] === 'Pelaporan Insiden') {
                        $menuItem['label'] = $incidentCount;
                    }
                }
                config(['adminlte.menu' => $menu]);
            }
        } catch (\Exception $e) {
            // Fail silently if database is not available
            View::share('globalRevisionCount', 0);
            View::share('globalComplaintCount', 0);
            View::share('globalIncidentCount', 0);
        }
        
        return $next($request);
    }
}
