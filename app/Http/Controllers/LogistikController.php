<?php

namespace App\Http\Controllers;

use App\Models\Document as ModelsDocument;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogistikController extends Controller
{
    function index(){
        return view('logistik.index');
    }
    
    function getDataPuskesmasByResi($resi){
        $data = Puskesmas::with('pengiriman','document','district','district.regency','district.regency.province')->whereHas('pengiriman', function($query) use ($resi) {
            $query->where('resi', $resi);
        })->first();
  
        if($data->document->is_verified_bast){
            return response()->json([
                'success' => false,
                'message' => 'File BAST untuk pengiriman ini sudah diunggah dan diverifikasi.'
            ], 400);
        }
        if($data){
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }
    function uploadBast(Request $request){
        $request->validate([
            'resi' => 'required|string|exists:pengiriman,resi',
            'bast_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', 
        ]);

        try {
            $pengiriman = Pengiriman::where('resi', $request->resi)->first();
            if(!$pengiriman){
                return response()->json([
                    'success' => false,
                    'message' => 'Pengiriman dengan resi tersebut tidak ditemukan.'
                ], 404);
            }

            // Simpan file BAST
            $file = $request->file('bast_file');
            $filename = 'BAST_' . $pengiriman->resi . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('bast_files', $filename, 'public');

            // Update record pengiriman dengan path file BAST
            $document = ModelsDocument::where('puskesmas_id', $pengiriman->puskesmas_id)->first();
            $document->bast = $filePath;
            $document->save();

            return response()->json([
                'success' => true,
                'message' => 'File BAST berhasil diunggah.',
                'data' => [
                    'bast_file_url' => asset('storage/' . $filePath)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading BAST: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah file BAST.' . $e->getMessage()
            ], 500);
        }
    }
}