<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keluhan;
use App\Models\KategoriKeluhan;
use App\Models\StatusKeluhan;

class KeluhanController extends Controller
{
    public function fetchData(Request $request)
    {
        // Get the puskesmas_id from the request
        $puskesmasId = $request->input('puskesmas_id');

        if (!$puskesmasId) {
            return response()->json([
                'success' => false,
                'message' => 'Puskesmas ID is required'
            ], 400);
        }

        try {
            // Get keluhan data with relationships
            $keluhan = Keluhan::with(['kategoriKeluhan', 'statusKeluhan', 'reporter'])
                ->where('puskesmas_id', $puskesmasId)
                ->orderBy('reported_date', 'desc')
                ->get();

            // Format data for DataTable
            $formattedData = $keluhan->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal_dilaporkan' => $item->reported_date ? $item->reported_date->format('d-m-Y') : '-',
                    'tanggal_dilaporkan_raw' => $item->reported_date,
                    'keluhan' => $item->reported_issue ?? '-',
                    'kategori_keluhan' => $item->kategoriKeluhan->kategori ?? '-',
                    'jumlah_downtime' => $item->total_downtime ? $item->total_downtime . ' hari' : '-',
                    'tanggal_selesai' => $item->resolved_date ? $item->resolved_date->format('d-m-Y') : '-',
                    'tanggal_selesai_raw' => $item->resolved_date,
                    'status' => $item->statusKeluhan->status ?? '-',
                    'status_id' => $item->status_id,
                    'reported_by' => $item->reporter->name ?? '-',
                    'action_taken' => $item->action_taken ?? '-',
                    'catatan' => $item->catatan ?? '-',
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
}
