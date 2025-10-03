<?php

namespace App\Http\Middleware;

use App\Models\Puskesmas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class NewAccountPuskesmas 
{
    public function handle(Request $request, Closure $next)
    {
        $puskesmasPassword =   Puskesmas::where('id', auth()->user()->puskesmas_id)
            ->first();
        if(auth()->check() && Hash::check($puskesmasPassword->npsn, auth()->user()->password)){
            if(auth()->user()->is_new_account){
                return $next($request);
            } else {
                return redirect('dashboard-puskesmas');
            }
        }

        return redirect('login');
    }
}