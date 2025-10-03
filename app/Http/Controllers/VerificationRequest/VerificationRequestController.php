<?php

namespace App\Http\Controllers\VerificationRequest;

use App\Http\Controllers\Controller;
use App\Models\Puskesmas;
use App\Models\Tahapan;
use Illuminate\Http\Request;

class VerificationRequestController extends Controller
{
    /**
     * Display a listing of Puskesmas that have shipment date (tgl_pengiriman) filled.
     */
    public function index( $statusVerifikasi=null)
    {
        // Optional filters could be added later (province, regency, etc.)
        if($statusVerifikasi == 'uji-fungsi'){
            $puskesmas = Puskesmas::query()
            ->with([
                'district.regency.province',
                'ujiFungsi:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ])
            ->whereHas('pengiriman')
            ->whereHas('ujiFungsi')
            ->whereDoesntHave('document')
            ->orderBy('name')
            ->get();
        }else if($statusVerifikasi == 'documents'){
            $puskesmas = Puskesmas::query()
            ->with([
                'district.regency.province',
                'ujiFungsi:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ])
            ->whereHas('pengiriman')
            ->whereHas('ujiFungsi')
            ->whereHas('document')
            ->orderBy('name')
            ->get();
        }else{
                $puskesmas = Puskesmas::query()
            ->with([
                'district.regency.province',
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ])
            ->whereHas('pengiriman')
            ->whereDoesntHave('ujiFungsi')
            ->whereDoesntHave('document')
            ->orderBy('name')
            ->get();

        }

        return view('verification-request.index', [
            'verificationRequests' => $puskesmas,
        ]);
    }

    /**
     * Return JSON list filtered by province/regency/district while still requiring tgl_pengiriman not null
     */
    public function fetch(Request $request, $statusVerifikasi=null)
    {
        $provinceId = $request->get('province_id');
        $regencyId  = $request->get('regency_id');
        $districtId = $request->get('district_id');

        $query = Puskesmas::query()
            ->with([
                'district.regency.province',
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ]);

        if($statusVerifikasi == 'uji-fungsi'){
             $query->whereHas('pengiriman')
            ->whereHas('ujiFungsi')
            ->whereDoesntHave('document');
        }else if($statusVerifikasi == 'documents'){
             $query->whereHas('pengiriman')
            ->whereHas('ujiFungsi')
            ->whereHas('document');
        }else{
             $query->whereHas('pengiriman')
            ->whereDoesntHave('ujiFungsi')
            ->whereDoesntHave('document');
        }

        if ($districtId) {
            $query->where('district_id', $districtId);
        } elseif ($regencyId) {
            $query->whereHas('district.regency', function($q) use ($regencyId){
                $q->where('id', $regencyId);
            });
        } elseif ($provinceId) {
            $query->whereHas('district.regency.province', function($q) use ($provinceId){
                $q->where('id', $provinceId);
            });
        }


        $data = $query->orderBy('name')->get()->map(function($p){
            return [
                'id' => $p->id,
                'name' => $p->name,
                'province' => $p->district->regency->province->name ?? '-',
                'regency' => $p->district->regency->name ?? '-',
                'district' => $p->district->name ?? '-',
                'tgl_pengiriman' => ($p->pengiriman && $p->pengiriman->tgl_pengiriman) ? $p->pengiriman->tgl_pengiriman->format('d-m-Y') : null,
                'verif_kemenkes' => (bool) ($p->pengiriman->verif_kemenkes ?? false),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Show detail page for a specific Puskesmas.
     */
    public function detail(string $id)
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
        ->findOrFail($id);

        $tahapan = Tahapan::orderBy('tahap_ke')->get();

        return view('verification-request.detail', [
            'puskesmas' => $puskesmas,
            'tahapan' => $tahapan,
        ]);
    }
}
