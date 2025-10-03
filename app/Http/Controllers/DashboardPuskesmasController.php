<?php

namespace App\Http\Controllers;

use App\Models\Puskesmas;
use App\Models\Tahapan;

class DashboardPuskesmasController extends Controller
{
    public function index()
    {
        $puskesmas = Puskesmas::with([
            'district.regency.province',
            'pengiriman.tahapan',
            'ujiFungsi',
            'document'
        ])
        ->findOrFail(auth()->user()->puskesmas_id);

        $tahapan = Tahapan::orderBy('tahap_ke')->get();

        return view('verification-request.detail', [
            'puskesmas' => $puskesmas,
            'tahapan' => $tahapan,
        ]);
    }
}       