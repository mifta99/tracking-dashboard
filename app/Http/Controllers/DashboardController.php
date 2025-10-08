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
        $countDataProvince = Puskesmas::query()
            ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
            ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
            ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
            ->distinct()
            ->count('provinces.id');

        $countRegency = Puskesmas::query()
        ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
        ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
        ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
        ->distinct()
        ->count('regencies.id');

        $countDistrict = Puskesmas::query()
        ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
        ->join('regencies', 'regencies.id', '=', 'districts.regency_id')
        ->join('provinces', 'provinces.id', '=', 'regencies.province_id')
        ->distinct()
        ->count('districts.id');


        $tahapan = Tahapan::all();
        return view('dashboard', ['countPuskesmas' => $dataPuskesmasCount, 'dataStatus' => $dataStatus, 'tahapan' => $tahapan , 'countDataProvince' => $countDataProvince, 'countRegency' => $countRegency, 'countDistrict' => $countDistrict]);
    }
}
