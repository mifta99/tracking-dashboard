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
            'equipment' => function($query) {
                $query->latest('id');
            },
            'ujiFungsi',
            'document'
        ])
        ->findOrFail(auth()->user()->puskesmas_id);

        $tahapan = Tahapan::orderBy('tahap_ke')->get();

        // Get revision data for documents - only filter by is_verified = 0
        $revisions = [
            // UjiFungsi document revisions
            'instalasi' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 3)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            'uji_fungsi' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 4)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            'pelatihan' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 5)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            // Document table revisions
            'kalibrasi' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 1)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            'bast' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 2)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            'basto' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 6)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
            'aspak' => $puskesmas->revisions()
                ->where('jenis_dokumen_id', 7)
                ->where('is_verified', 0)
                ->latest()
                ->first(),
        ];

        return view('verification-request.detail', [
            'puskesmas' => $puskesmas,
            'tahapan' => $tahapan,
            'revisions' => $revisions,
        ]);
    }
}
