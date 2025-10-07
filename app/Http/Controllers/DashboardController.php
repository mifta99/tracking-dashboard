<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\Tahapan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $dataPuskesmasCount = Puskesmas::count();
        foreach (Tahapan::all() as $tahapan) {
            $dataStatus[$tahapan->tahapan] = Pengiriman::where('tahapan_id', $tahapan->id)->count();
        }
        $tahapan = Tahapan::all();
        return view('dashboard', ['countPuskesmas' => $dataPuskesmasCount, 'dataStatus' => $dataStatus, 'tahapan' => $tahapan]);
    }
}
