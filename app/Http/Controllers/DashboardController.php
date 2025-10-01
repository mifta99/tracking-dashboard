<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Puskesmas;
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
        $dataStatus = collect(
            [
                'shipment_process' =>Pengiriman::where('tahapan_id', 1)->count(),
                'on_delivery' => Pengiriman::where('tahapan_id', 2)->count(),
                'received' => Pengiriman::where('tahapan_id', 3)->count(),
                'installation' => Pengiriman::where('tahapan_id', 4)->count(),
                'function_test' => Pengiriman::where('tahapan_id', 5)->count(),
                'item_training' => Pengiriman::where('tahapan_id', 6)->count(),
                'basto' => Pengiriman::where('tahapan_id', 7)->count(),
                'aspak' => Pengiriman::where('tahapan_id', 8)->count(),
            ]
        );
        return view('dashboard', ['countPuskesmas' => $dataPuskesmasCount, 'dataStatus' => $dataStatus]);
    }
}
