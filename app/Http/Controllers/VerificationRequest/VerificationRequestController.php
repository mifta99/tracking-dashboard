<?php

namespace App\Http\Controllers\VerificationRequest;

use App\Http\Controllers\Controller;
use App\Models\OpsiKeluhan;
use App\Models\KalibrasiMaster;
use App\Models\MaintenanceMaster;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\Tahapan;
use App\Models\UjiFungsi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VerificationRequestController extends Controller
{
    /**
     * Display a listing of Puskesmas that have shipment date (tgl_pengiriman) filled.
     */
    public function index()
    {
        return view('verification-request.index');
    }

    /**
     * Return JSON list filtered by province/regency/district while still requiring tgl_pengiriman not null
     */
    public function fetch(Request $request, $statusVerifikasi = null)
    {
        $provinceId = $request->get('province_id');
        $regencyId  = $request->get('regency_id');
        $districtId = $request->get('district_id');
        $statusFilter = $statusVerifikasi ?? $request->get('status');
        $statusVerificationFilter = $request->get('status_filter');

        $draw   = (int) $request->get('draw', 1);
        $start  = max((int) $request->get('start', 0), 0);
        $length = (int) $request->get('length', 25);
        if ($length < 0) {
            $length = -1;
        }

        $query = Puskesmas::query()
            ->with([
                'district.regency.province',
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes,updated_at',
                'document:id,puskesmas_id,kalibrasi,is_verified_kalibrasi,bast,is_verified_bast,aspak,is_verified_aspak,basto,is_verified_basto,verif_kemenkes,tgl_verif_kemenkes,updated_at',
                'ujiFungsi:id,puskesmas_id,doc_instalasi,is_verified_instalasi,doc_uji_fungsi,is_verified_uji_fungsi,doc_pelatihan,is_verified_pelatihan,updated_at',
                'revisions:id,puskesmas_id,jenis_dokumen_id,is_resolved,is_verified,created_at,updated_at'
            ]);

        if ($districtId) {
            $query->where('district_id', $districtId);
        } elseif ($regencyId) {
            $query->whereHas('district.regency', function ($q) use ($regencyId) {
                $q->where('id', $regencyId);
            });
        } elseif ($provinceId) {
            $query->whereHas('district.regency.province', function ($q) use ($provinceId) {
                $q->where('id', $provinceId);
            });
        }

        self::addPendingVerificationConstraints($query);

        $recordsTotal = (clone $query)->count();

        $searchValue = $request->input('search.value');
        if ($searchValue && strlen(trim($searchValue)) >= 2) {
            $searchValue = trim($searchValue);
            $like = '%' . $searchValue . '%';

            $query->where(function (Builder $searchQuery) use ($like) {
                $searchQuery->where('name', 'like', $like)
                    ->orWhereHas('district', function (Builder $districtQuery) use ($like) {
                        $districtQuery->where('name', 'like', $like)
                            ->orWhereHas('regency', function (Builder $regencyQuery) use ($like) {
                                $regencyQuery->where('name', 'like', $like)
                                    ->orWhereHas('province', function (Builder $provinceQuery) use ($like) {
                                        $provinceQuery->where('name', 'like', $like);
                                    });
                            });
                    });
            });
        }

        $orderColumnIndex = (int) $request->input('order.0.column', 1);
        $orderDirection = strtolower($request->input('order.0.dir', 'desc')) === 'desc' ? 'desc' : 'asc';

        switch ($orderColumnIndex) {
            case 5:
                $query->orderBy(
                    Pengiriman::select('tgl_pengiriman')
                        ->whereColumn('pengiriman.puskesmas_id', 'puskesmas.id')
                        ->limit(1),
                    $orderDirection
                );
                break;
            default:
                $query->orderBy(
                    DB::raw('GREATEST(
                        COALESCE(puskesmas.updated_at, "1970-01-01"),
                        COALESCE((SELECT MAX(updated_at) FROM pengiriman WHERE pengiriman.puskesmas_id = puskesmas.id), "1970-01-01"),
                        COALESCE((SELECT updated_at FROM documents WHERE documents.puskesmas_id = puskesmas.id), "1970-01-01"),
                        COALESCE((SELECT updated_at FROM uji_fungsi WHERE uji_fungsi.puskesmas_id = puskesmas.id), "1970-01-01"),
                        COALESCE((SELECT MAX(updated_at) FROM revisions WHERE revisions.puskesmas_id = puskesmas.id), "1970-01-01")
                    )'),
                    $orderDirection
                );
                break;
        }

        $dataCollection = $query->get()->map(function (Puskesmas $puskesmas) {
            $pengiriman = $puskesmas->pengiriman;
            $document = $puskesmas->document;
            $ujiFungsi = $puskesmas->ujiFungsi;
            $revisions = $puskesmas->revisions ?? collect();
            $tglPengiriman = $pengiriman && $pengiriman->tgl_pengiriman
                ? $pengiriman->tgl_pengiriman->translatedFormat('d M Y')
                : null;

            $hasUnresolvedRevision = function (int $jenisDokumen) use ($revisions): bool {
                return $revisions->first(function ($revision) use ($jenisDokumen) {
                    return (int) $revision->jenis_dokumen_id === $jenisDokumen && !(bool) $revision->is_resolved;
                }) !== null;
            };

            $pendingDocs = [];
            $pendingFlags = [
                'verif_kalibrasi' => false,
                'verif_bast' => false,
                'verif_aspak' => false,
                'verif_basto' => false,
                'verif_instalasi' => false,
                'verif_uji_fungsi' => false,
                'verif_pelatihan_alat' => false,
            ];

            if ($document) {
                if (!(bool) $document->is_verified_kalibrasi && $document->kalibrasi !== null && !$hasUnresolvedRevision(1)) {
                    $pendingDocs[] = 'Kalibrasi';
                    $pendingFlags['verif_kalibrasi'] = true;
                }
                if (!(bool) $document->is_verified_bast && $document->bast !== null && !$hasUnresolvedRevision(2)) {
                    $pendingDocs[] = 'BAST';
                    $pendingFlags['verif_bast'] = true;
                }
                if (!(bool) $document->is_verified_aspak && $document->aspak !== null && !$hasUnresolvedRevision(7)) {
                    $pendingDocs[] = 'ASPAK';
                    $pendingFlags['verif_aspak'] = true;
                }
                if (!(bool) $document->is_verified_basto && $document->basto !== null && !$hasUnresolvedRevision(6)) {
                    $pendingDocs[] = 'BASTO';
                    $pendingFlags['verif_basto'] = true;
                }
            }

            if ($ujiFungsi) {
                if (!(bool) $ujiFungsi->is_verified_instalasi && $ujiFungsi->doc_instalasi !== null && !$hasUnresolvedRevision(3)) {
                    $pendingDocs[] = 'Instalasi';
                    $pendingFlags['verif_instalasi'] = true;
                }
                if (!(bool) $ujiFungsi->is_verified_uji_fungsi && $ujiFungsi->doc_uji_fungsi !== null && !$hasUnresolvedRevision(4)) {
                    $pendingDocs[] = 'Uji Fungsi';
                    $pendingFlags['verif_uji_fungsi'] = true;
                }
                if (!(bool) $ujiFungsi->is_verified_pelatihan && $ujiFungsi->doc_pelatihan !== null && !$hasUnresolvedRevision(5)) {
                    $pendingDocs[] = 'Pelatihan';
                    $pendingFlags['verif_pelatihan_alat'] = true;
                }
            }

            $pendingCount = count($pendingDocs);

            return [
                'id' => $puskesmas->id,
                'name' => $puskesmas->name,
                'province' => optional(optional(optional($puskesmas->district)->regency)->province)->name ?? '-',
                'regency' => optional(optional($puskesmas->district)->regency)->name ?? '-',
                'district' => optional($puskesmas->district)->name ?? '-',
                'tgl_pengiriman' => $tglPengiriman,
                'pending_docs' => $pendingDocs,
                'pending_count' => $pendingCount,
                'has_pending_verification' => $pendingCount > 0,
            ] + $pendingFlags;
        });

        $dataCollection = $dataCollection->filter(function (array $item) {
            return $item['has_pending_verification'];
        })->values();

        if ($statusVerificationFilter) {
            $filters = array_filter(array_map('trim', explode(',', $statusVerificationFilter)));
            if (!empty($filters)) {
                $dataCollection = $dataCollection->filter(function (array $item) use ($filters) {
                    $matchesFilter = false;

                    foreach ($filters as $filter) {
                        switch ($filter) {
                            case 'has_kalibrasi':
                                if ($item['verif_kalibrasi']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_bast':
                                if ($item['verif_bast']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_instalasi':
                                if ($item['verif_instalasi']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_uji_fungsi':
                                if ($item['verif_uji_fungsi']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_pelatihan':
                                if ($item['verif_pelatihan_alat']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_aspak':
                                if ($item['verif_aspak']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'has_basto':
                                if ($item['verif_basto']) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'count_1':
                                if ((int) $item['pending_count'] === 1) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'count_2':
                                if ((int) $item['pending_count'] === 2) {
                                    $matchesFilter = true;
                                }
                                break;
                            case 'count_3_plus':
                                if ((int) $item['pending_count'] >= 3) {
                                    $matchesFilter = true;
                                }
                                break;
                        }
                    }

                    return $matchesFilter;
                })->values();
            }
        }

        $recordsFiltered = $dataCollection->count();

        if ($length !== -1) {
            $pageSize = $length > 0 ? $length : 25;
            $dataCollection = $dataCollection->slice($start, $pageSize)->values();
        }

        $responseData = $dataCollection->all();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $responseData,
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
        $maintenance = MaintenanceMaster::all();
        $kalibrasi = KalibrasiMaster::all();
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
            'maintenance' => $maintenance,
            'kalibrasi' => $kalibrasi,
            'opsiKeluhan' => OpsiKeluhan::with('kategoriKeluhan')->orderBy('opsi', 'asc')->get(),
        ]);
    }

    /**
     * Apply constraints so that only Puskesmas with documents truly ready for verification are returned.
     */
    protected static function addPendingVerificationConstraints(Builder $query): Builder
    {
        $pendingDocuments = [
            ['relation' => 'document', 'field' => 'kalibrasi', 'verified_column' => 'is_verified_kalibrasi', 'revision_type' => 1],
            ['relation' => 'document', 'field' => 'bast', 'verified_column' => 'is_verified_bast', 'revision_type' => 2],
            ['relation' => 'document', 'field' => 'aspak', 'verified_column' => 'is_verified_aspak', 'revision_type' => 7],
            ['relation' => 'document', 'field' => 'basto', 'verified_column' => 'is_verified_basto', 'revision_type' => 6],
            ['relation' => 'ujiFungsi', 'field' => 'doc_instalasi', 'verified_column' => 'is_verified_instalasi', 'revision_type' => 3],
            ['relation' => 'ujiFungsi', 'field' => 'doc_uji_fungsi', 'verified_column' => 'is_verified_uji_fungsi', 'revision_type' => 4],
            ['relation' => 'ujiFungsi', 'field' => 'doc_pelatihan', 'verified_column' => 'is_verified_pelatihan', 'revision_type' => 5],
        ];

        return $query->where(function (Builder $pendingQuery) use ($pendingDocuments) {
            foreach ($pendingDocuments as $document) {
                $pendingQuery->orWhere(function (Builder $subQuery) use ($document) {
                    $subQuery
                        ->whereHas($document['relation'], function (Builder $relationQuery) use ($document) {
                            $relationQuery
                                ->where($document['verified_column'], 0)
                                ->whereNotNull($document['field']);
                        })
                        ->whereDoesntHave('revisions', function (Builder $revQuery) use ($document) {
                            $revQuery
                                ->where('jenis_dokumen_id', $document['revision_type'])
                                ->where('is_resolved', false);
                        });
                });
            }
        });
    }
    public static function getVerificationRequestCount()
    {
        $query = Puskesmas::query();

        static::addPendingVerificationConstraints($query);

        return $query->count();
    }
}
