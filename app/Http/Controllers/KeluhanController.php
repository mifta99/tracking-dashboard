<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Keluhan;
use App\Models\KategoriKeluhan;
use App\Models\StatusKeluhan;

class KeluhanController extends Controller
{
    public function fetchData(Request $request)
    {
        try {
            // Build query with relationships
            $query = Keluhan::with(['kategoriKeluhan', 'statusKeluhan', 'reporter', 'puskesmas.district.regency.province']);

            // For detail page: filter by specific puskesmas and include all statuses
            if ($request->has('puskesmas_id') && $request->puskesmas_id) {
                $query->where('puskesmas_id', $request->puskesmas_id);
            } else {
                // Handle status filtering based on request parameter
                if ($request->has('show_completed') && $request->show_completed == 'true') {
                    // Show only completed complaints
                    $query->whereHas('statusKeluhan', function($q) {
                        $q->where('status', 'selesai');
                    });
                } else {
                    // For index page: automatically exclude 'selesai' status (default behavior)
                    $query->whereHas('statusKeluhan', function($q) {
                        $q->where('status', '!=', 'selesai');
                    });
                }
            }

            // For puskesmas users, only show their own keluhan
            if (auth()->user()->role_id == 1) {
                $query->where('puskesmas_id', auth()->user()->puskesmas_id);
            }

            $keluhan = $query->orderBy('reported_date', 'desc')->get();

            // Format data for DataTable
            $formattedData = $keluhan->map(function ($item) {
                $puskesmas = $item->puskesmas;
                $district = $puskesmas ? $puskesmas->district : null;
                $regency = $district ? $district->regency : null;
                $province = $regency ? $regency->province : null;

                return [
                    'id' => $item->id,
                    'tanggal_dilaporkan' => $item->reported_date ? $item->reported_date->translatedFormat('d M Y') : '-',
                    'tanggal_dilaporkan_raw' => $item->reported_date,
                    'keluhan' => $item->reported_subject ?? '-',
                    'detail_keluhan' => $item->reported_issue ?? '-',
                    'kategori_keluhan' => $item->kategoriKeluhan->kategori ?? '-',
                    'jumlah_downtime' => $item->total_downtime ? $item->total_downtime . ' hari' : '-',
                    'tanggal_selesai' => $item->resolved_date ? $item->resolved_date->translatedFormat('d M Y') : '-',
                    'tanggal_selesai_raw' => $item->resolved_date,
                    'status' => $item->statusKeluhan->status ?? '-',
                    'status_id' => $item->status_id,
                    'reported_by' => $item->reporter->name ?? '-',
                    'action_taken' => $item->action_taken ?? '-',
                    'catatan' => $item->catatan ?? '-',
                    // Location information for index page
                    'puskesmas_name' => $puskesmas->name ?? '-',
                    'district_name' => $district->name ?? '-',
                    'regency_name' => $regency->name ?? '-',
                    'province_name' => $province->name ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching keluhan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData()
    {
        try {
            $kategoris = KategoriKeluhan::all();
            $statuses = StatusKeluhan::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'kategoris' => $kategoris,
                    'statuses' => $statuses
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching master data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total complaint count for menu badge
     * Similar to DaftarRevisiController::getTotalRevisionCount()
     */
    public static function getTotalComplaintCount()
    {
        try {
            // Count complaints that are not resolved (status != 'selesai')
            return Keluhan::whereHas('statusKeluhan', function($query) {
                $query->where('status', '!=', 'selesai');
            })->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get complaint counts by status for dashboard cards
     */
    public function getStatusCounts()
    {
        try {
            $counts = [
                'baru' => 0,
                'proses' => 0,
                'selesai' => 0,
                'total' => 0
            ];

            // Get counts by status
            $statusCounts = Keluhan::with('statusKeluhan')
                ->get()
                ->groupBy(function($keluhan) {
                    return strtolower($keluhan->statusKeluhan->status ?? 'unknown');
                })
                ->map(function($group) {
                    return $group->count();
                });

            $counts['baru'] = $statusCounts->get('baru', 0);
            $counts['proses'] = $statusCounts->get('proses', 0);
            $counts['selesai'] = $statusCounts->get('selesai', 0);
            $counts['total'] = $counts['baru'] + $counts['proses'] + $counts['selesai'];

            return response()->json([
                'success' => true,
                'data' => $counts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching status counts: ' . $e->getMessage(),
                'data' => ['baru' => 0, 'proses' => 0, 'selesai' => 0, 'total' => 0]
            ], 500);
        }
    }
}
