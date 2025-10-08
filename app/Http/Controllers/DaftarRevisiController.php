<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Revision;
use App\Models\Puskesmas;
use App\Models\JenisDokumen;

class DaftarRevisiController extends Controller
{
    public function index()
    {
        return view('daftar-revisi.index');
    }

    public function fetchData()
    {
        // Get all jenis dokumen for dynamic mapping
        $jenisDokumens = JenisDokumen::pluck('nama_dokumen', 'id');

        // Get unresolved revisions with puskesmas data (correct relationships)
        $query = Revision::with(['puskesmas.district.regency.province', 'jenisDokumen'])
            ->where('is_resolved', false)
            ->where('is_verified', false);

        // Apply filters if provided
        if (request('province_id')) {
            $query->whereHas('puskesmas.district.regency.province', function ($q) {
                $q->where('id', request('province_id'));
            });
        }

        if (request('regency_id')) {
            $query->whereHas('puskesmas.district.regency', function ($q) {
                $q->where('id', request('regency_id'));
            });
        }

        if (request('district_id')) {
            $query->whereHas('puskesmas.district', function ($q) {
                $q->where('id', request('district_id'));
            });
        }

        $revisions = $query->orderBy('created_at', 'desc')->get();

        // Group revisions by puskesmas to show multiple documents per puskesmas
        $groupedRevisions = $revisions->groupBy('puskesmas_id')->map(function ($revisionGroup) use ($jenisDokumens) {
            $firstRevision = $revisionGroup->first();
            $puskesmas = $firstRevision->puskesmas;

            // Get document types dynamically from jenis_dokumens table
            $documentTypes = $revisionGroup->map(function ($revision) use ($jenisDokumens) {
                return $jenisDokumens[$revision->jenis_dokumen_id] ?? 'Unknown';
            })->unique()->values();

            return [
                'puskesmas_id' => $puskesmas->id,
                'provinsi' => $puskesmas->district->regency->province->name ?? '-',
                'kabupaten' => $puskesmas->district->regency->name ?? '-',
                'kecamatan' => $puskesmas->district->name ?? '-',
                'nama_puskesmas' => $puskesmas->name,
                'document_types' => $documentTypes->toArray(),
                'revision_count' => $revisionGroup->count(),
                'latest_created_at' => $firstRevision->created_at->format('d M Y'),
                'created_at_raw' => $firstRevision->created_at,
                'sort_date' => $firstRevision->created_at->timestamp // For sorting purposes
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $groupedRevisions
        ]);
    }

    public static function getTotalRevisionCount()
    {
        // Count unresolved revisions grouped by puskesmas to match the datatable count
        $revisions = Revision::with(['puskesmas.district.regency.province'])
            ->where('is_resolved', false)
            ->where('is_verified', false)
            ->get();

        // Group by puskesmas_id and count unique puskesmas with revisions
        return $revisions->groupBy('puskesmas_id')->count();
    }
}
