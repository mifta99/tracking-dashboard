<?php

namespace App\Http\Controllers\VerificationRequest;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\Revision;
use App\Models\Tahapan;
use App\Models\UjiFungsi;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                'pengiriman:id,puskesmas_id,tgl_pengiriman,verif_kemenkes,tgl_verif_kemenkes',
                'document:id,puskesmas_id,kalibrasi,is_verified_kalibrasi,bast,is_verified_bast,aspak,is_verified_aspak,basto,is_verified_basto,verif_kemenkes,tgl_verif_kemenkes',
                'ujiFungsi:id,puskesmas_id,doc_instalasi,is_verified_instalasi,doc_uji_fungsi,is_verified_uji_fungsi,doc_pelatihan,is_verified_pelatihan'
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

        $query->where(function ($q) {
            $q->whereHas('document', function ($docQuery) {
                $docQuery->where(function ($subQuery) {
                    $subQuery->where(function ($inner) {
                        $inner->where('is_verified_kalibrasi', 0)
                            ->whereNotNull('kalibrasi');
                    })->orWhere(function ($inner) {
                        $inner->where('is_verified_bast', 0)
                            ->whereNotNull('bast');
                    })->orWhere(function ($inner) {
                        $inner->where('is_verified_aspak', 0)
                            ->whereNotNull('aspak');
                    })->orWhere(function ($inner) {
                        $inner->where('is_verified_basto', 0)
                            ->whereNotNull('basto');
                    });
                });
            })->orWhereHas('ujiFungsi', function ($ujiQuery) {
                $ujiQuery->where(function ($subQuery) {
                    $subQuery->where(function ($inner) {
                        $inner->where('is_verified_instalasi', 0)
                            ->whereNotNull('doc_instalasi');
                    })->orWhere(function ($inner) {
                        $inner->where('is_verified_uji_fungsi', 0)
                            ->whereNotNull('doc_uji_fungsi');
                    })->orWhere(function ($inner) {
                        $inner->where('is_verified_pelatihan', 0)
                            ->whereNotNull('doc_pelatihan');
                    });
                });
            })->orWhereHas('revisions', function ($revQuery) {
                $revQuery->where('is_resolved', 0);
            });
        });


        $recordsTotal = (clone $query)->count();

        $searchValue = $request->input('search.value');
        if ($searchValue && strlen(trim($searchValue)) >= 2) {
            $searchValue = trim($searchValue);
            $like = '%' . $searchValue . '%';

            $query->where(function ($searchQuery) use ($like) {
                $searchQuery->where('name', 'like', $like)
                    ->orWhereHas('district', function ($districtQuery) use ($like) {
                        $districtQuery->where('name', 'like', $like)
                            ->orWhereHas('regency', function ($regencyQuery) use ($like) {
                                $regencyQuery->where('name', 'like', $like)
                                    ->orWhereHas('province', function ($provinceQuery) use ($like) {
                                        $provinceQuery->where('name', 'like', $like);
                                    });
                            });
                    });
            });
        }

        $recordsFiltered = (clone $query)->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 1);
        $orderDirection = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        switch ($orderColumnIndex) {
            case 1:
                $query->orderBy('name', $orderDirection);
                break;
            case 5:
                $query->orderBy(
                    Pengiriman::select('tgl_pengiriman')
                        ->whereColumn('pengiriman.puskesmas_id', 'puskesmas.id')
                        ->limit(1),
                    $orderDirection
                );
                break;
            default:
                $query->orderBy('name', 'asc');
                break;
        }

        if ($length !== -1) {
            $query->skip($start)->take($length > 0 ? $length : 25);
        }

        $data = $query->get()->map(function (Puskesmas $puskesmas) {
            $pengiriman = $puskesmas->pengiriman;
            $document = $puskesmas->document;
            $ujiFungsi = $puskesmas->ujiFungsi;
            $tglPengiriman = $pengiriman && $pengiriman->tgl_pengiriman
                ? $pengiriman->tgl_pengiriman->translatedFormat('d-m-Y')
                : null;

            // Collect documents that need verification
            $pendingDocs = [];

            if ($document) {
                if (!(bool)$document->is_verified_kalibrasi && $document->kalibrasi != null && $puskesmas->revisions->where('jenis_dokumen_id',1)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'Kalibrasi';
                }
                if (!(bool)$document->is_verified_bast && $document->bast != null && $puskesmas->revisions->where('jenis_dokumen_id',2)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'BAST';
                }
                if (!(bool)$document->is_verified_aspak && $document->aspak != null && $puskesmas->revisions->where('jenis_dokumen_id',7)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'ASPAK';
                }
                if (!(bool)$document->is_verified_basto && $document->basto != null && $puskesmas->revisions->where('jenis_dokumen_id',6)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'BASTO';
                }
            }

            if ($ujiFungsi) {
                if (!(bool)$ujiFungsi->is_verified_instalasi && $ujiFungsi->doc_instalasi != null && $puskesmas->revisions->where('jenis_dokumen_id',3)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'Instalasi';
                }
                if (!(bool)$ujiFungsi->is_verified_uji_fungsi && $ujiFungsi->doc_uji_fungsi != null && $puskesmas->revisions->where('jenis_dokumen_id',4)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'Uji Fungsi';
                }
                if (!(bool)$ujiFungsi->is_verified_pelatihan && $ujiFungsi->doc_pelatihan != null && $puskesmas->revisions->where('jenis_dokumen_id',5)->where('is_resolved',false)->sortByDesc('created_at')->first() == null) {
                    $pendingDocs[] = 'Pelatihan';
                }
            }

            return [
                'id' => $puskesmas->id,
                'name' => $puskesmas->name,
                'province' => optional(optional(optional($puskesmas->district)->regency)->province)->name ?? '-',
                'regency' => optional(optional($puskesmas->district)->regency)->name ?? '-',
                'district' => optional($puskesmas->district)->name ?? '-',
                'tgl_pengiriman' => $tglPengiriman,
                'pending_docs' => $pendingDocs,
                'pending_count' => count($pendingDocs),
                'has_pending_verification' => count($pendingDocs) > 0
            ];
        })->filter(function ($item) use ($statusVerificationFilter) {
            // Only show Puskesmas that have documents ready for verification
            if (!$item['has_pending_verification']) {
                return false;
            }

            // Apply status filter if specified
            if ($statusVerificationFilter) {
                $filters = explode(',', $statusVerificationFilter);
                $matchesFilter = false;

                foreach ($filters as $filter) {
                    $filter = trim($filter);
                    switch ($filter) {
                        case 'has_kalibrasi':
                            if ($item['verif_kalibrasi']) $matchesFilter = true;
                            break;
                        case 'has_bast':
                            if ($item['verif_bast']) $matchesFilter = true;
                            break;
                        case 'has_instalasi':
                            if ($item['verif_instalasi']) $matchesFilter = true;
                            break;
                        case 'has_uji_fungsi':
                            if ($item['verif_uji_fungsi']) $matchesFilter = true;
                            break;
                        case 'has_pelatihan':
                            if ($item['verif_pelatihan_alat']) $matchesFilter = true;
                            break;
                        case 'has_aspak':
                            if ($item['verif_aspak']) $matchesFilter = true;
                            break;
                        case 'has_basto':
                            if ($item['verif_basto']) $matchesFilter = true;
                            break;
                        case 'count_1':
                            if ($item['pending_count'] == 1) $matchesFilter = true;
                            break;
                        case 'count_2':
                            if ($item['pending_count'] == 2) $matchesFilter = true;
                            break;
                        case 'count_3_plus':
                            if ($item['pending_count'] >= 3) $matchesFilter = true;
                            break;
                    }
                }

                return $matchesFilter;
            }

            return true;
        })->values();

        // Update recordsFiltered to reflect the actual filtered count
        $actualFilteredCount = count($data);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $actualFilteredCount,
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
    public static function getVerificationRequestCount(){
        return Puskesmas::query()
            ->where(function ($q) {
                $q->whereHas('document', function ($docQuery) {
                    $docQuery->where(function ($subQuery) {
                        $subQuery->where(function ($inner) {
                            $inner->where('is_verified_kalibrasi', 0)
                                ->whereNotNull('kalibrasi');
                        })->orWhere(function ($inner) {
                            $inner->where('is_verified_bast', 0)
                                ->whereNotNull('bast');
                        })->orWhere(function ($inner) {
                            $inner->where('is_verified_aspak', 0)
                                ->whereNotNull('aspak');
                        })->orWhere(function ($inner) {
                            $inner->where('is_verified_basto', 0)
                                ->whereNotNull('basto');
                        });
                    });
                })
                ->orWhereHas('ujiFungsi', function ($ujiQuery) {
                    $ujiQuery->where(function ($subQuery) {
                        $subQuery->where(function ($inner) {
                            $inner->where('is_verified_instalasi', 0)
                                ->whereNotNull('doc_instalasi');
                        })->orWhere(function ($inner) {
                            $inner->where('is_verified_uji_fungsi', 0)
                                ->whereNotNull('doc_uji_fungsi');
                        })->orWhere(function ($inner) {
                            $inner->where('is_verified_pelatihan', 0)
                                ->whereNotNull('doc_pelatihan');
                        });
                    });
                });
            })
            ->count();
    }
}
