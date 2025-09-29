<?php

namespace App\Http\Controllers\Puskesmas\API;

use App\Http\Controllers\Controller;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class APIPuskesmasController extends Controller
{
    public function fetchData(Request $request)
    {
        try {
            Log::info('API fetchData called', $request->all());
            
            $query = Puskesmas::with(['district.regency.province']);
            
            // Filter by province
            if ($request->has('province_id') && !empty($request->province_id)) {
                $query->whereHas('district.regency.province', function($q) use ($request) {
                    $q->where('id', $request->province_id);
                });
            }
            
            // Filter by regency
            if ($request->has('regency_id') && !empty($request->regency_id)) {
                $query->whereHas('district.regency', function($q) use ($request) {
                    $q->where('id', $request->regency_id);
                });
            }
            
            // Filter by district
            if ($request->has('district_id') && !empty($request->district_id)) {
                $query->whereHas('district', function($q) use ($request) {
                    $q->where('id', $request->district_id);
                });
            }
            
            $data = $query->get();
            
            Log::info('Query result', ['count' => $data->count()]);
            
            foreach ($data as $item) {
                $item->setAttribute('provinsi', $item->district->regency->province->name ?? 'N/A');
                $item->setAttribute('kabupaten_kota', $item->district->regency->name ?? 'N/A');
                $item->setAttribute('kecamatan', $item->district->name ?? 'N/A');
            }
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetchData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }   

    /**
     * Fetch all provinces
     */
    public function fetchProvinces()
    {
        try {
            $provinces = \App\Models\Province::orderBy('name', 'asc')->get();
            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching provinces: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Fetch regencies based on province_id
     */
    public function fetchRegencies(Request $request)
    {
        try {
            $query = \App\Models\Regency::orderBy('name', 'asc');
            
            if ($request->has('province_id') && !empty($request->province_id)) {
                $query->where('province_id', $request->province_id);
            }
            
            $regencies = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $regencies,
                'count' => $regencies->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching regencies: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Fetch districts based on regency_id
     */
    public function fetchDistricts(Request $request)
    {
        try {
            $query = \App\Models\District::orderBy('name', 'asc');
            
            if ($request->has('regency_id') && !empty($request->regency_id)) {
                $query->where('regency_id', $request->regency_id);
            }
            
            $districts = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $districts,
                'count' => $districts->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching districts: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Test API connectivity
     */
    public function testConnection()
    {
        try {
            $provinceCount = \App\Models\Province::count();
            $regencyCount = \App\Models\Regency::count();
            $districtCount = \App\Models\District::count();
            $puskesmasCount = \App\Models\Puskesmas::count();
            
            return response()->json([
                'success' => true,
                'message' => 'API connection successful',
                'data' => [
                    'provinces' => $provinceCount,
                    'regencies' => $regencyCount,
                    'districts' => $districtCount,
                    'puskesmas' => $puskesmasCount,
                    'timestamp' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}


