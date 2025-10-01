<?php

namespace App\Http\Controllers\VerificationRequest;

use App\Http\Controllers\Controller;
use App\Models\Puskesmas;
use Illuminate\Http\Request;

class VerificationRequestController extends Controller
{
    /**
     * Display a listing of Puskesmas that have shipment date (tgl_pengiriman) filled.
     */
    public function index(Request $request)
    {
        // Optional filters could be added later (province, regency, etc.)
        $puskesmas = Puskesmas::query()
            ->with([
                'district.regency.province',
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ])
            ->whereHas('pengiriman', function ($q) {
                $q->whereNotNull('tgl_pengiriman');
            })
            ->orderBy('name')
            ->get();

        return view('verification-request.index', [
            'verificationRequests' => $puskesmas,
        ]);
    }

    /**
     * Return JSON list filtered by province/regency/district while still requiring tgl_pengiriman not null
     */
    public function fetch(Request $request)
    {
        $provinceId = $request->get('province_id');
        $regencyId  = $request->get('regency_id');
        $districtId = $request->get('district_id');

        $query = Puskesmas::query()
            ->with([
                'district.regency.province',
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,verif_kemenkes,tgl_verif_kemenkes'
            ])
            ->whereHas('pengiriman', function ($q) {
                $q->whereNotNull('tgl_pengiriman');
            });

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
    public function show(string $id)
    {
        $puskesmas = Puskesmas::with([
            'district.regency.province',
            'pengiriman',
            'ujiFungsi',
            'document'
        ])
        // ->whereHas('pengiriman', function($q){
        //     $q->whereNotNull('tgl_pengiriman');
        // })
        ->findOrFail($id);

        return view('verification-request.show', [
            'puskesmas' => $puskesmas,
        ]);
    }
}
