<?php

namespace App\Http\Controllers\Incident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Revision;
use App\Models\Puskesmas;
use App\Models\JenisDokumen;
use App\Models\Insiden;
use App\Models\KategoriInsiden;
use App\Models\Tahapan;
use App\Models\StatusInsiden;

class IncidentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $incidents = collect();
        
        // Load incidents based on user role
        if ($user->role_id === 1) { // Puskesmas user
            $incidents = Insiden::where('puskesmas_id', $user->puskesmas_id)
                ->with(['puskesmas', 'tahapan', 'status', 'kategoriInsiden', 'reporter'])
                ->latest()
                ->get();
        } else { // Admin users
            $incidents = Insiden::with(['puskesmas', 'tahapan', 'status', 'kategoriInsiden', 'reporter'])
                ->latest()
                ->get();
        }
        
        return view('reported-incidents.index', compact('incidents'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tgl_kejadian' => 'required|date',
            'kategori_id' => 'required|integer|exists:kategori_insiden,id',
            'nama_korban' => 'required|string|max:255',
            'bagian' => 'required|string|max:255',
            'insiden' => 'required|string|max:500',
            'kronologis' => 'required|string',
            'tahapan_id' => 'nullable|integer|exists:tahapan,id',
            'status_id' => 'nullable|integer|exists:status_insiden,id',
            'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ], [
            'tgl_kejadian.required' => 'Tanggal kejadian harus diisi.',
            'kategori_id.required' => 'Kategori insiden harus dipilih.',
            'nama_korban.required' => 'Nama korban harus diisi.',
            'bagian.required' => 'Bagian/unit harus diisi.',
            'insiden.required' => 'Judul insiden harus diisi.',
            'kronologis.required' => 'Kronologis kejadian harus diisi.',
            'dokumentasi.*.mimes' => 'File dokumentasi harus berformat JPG, JPEG, PNG, atau PDF.',
            'dokumentasi.*.max' => 'Ukuran file dokumentasi maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $dokumentasiPaths = [];

            // Handle file uploads
            if ($request->hasFile('dokumentasi')) {
                foreach ($request->file('dokumentasi') as $file) {
                    $path = $file->store('incidents/dokumentasi', 'public');
                    $dokumentasiPaths[] = Storage::url($path);
                }
            }

            // Create incident
            $incident = Insiden::create([
                'puskesmas_id' => $user->puskesmas_id ?? null,
                'tahapan_id' => $request->tahapan_id,
                'status_id' => $request->status_id,
                'kategori_id' => $request->kategori_id,
                'tgl_kejadian' => $request->tgl_kejadian,
                'nama_korban' => $request->nama_korban,
                'bagian' => $request->bagian,
                'insiden' => $request->insiden,
                'kronologis' => $request->kronologis,
                'reported_by' => $user->id,
                'dokumentasi' => !empty($dokumentasiPaths) ? implode(',', $dokumentasiPaths) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Insiden berhasil dilaporkan.',
                'incident_id' => $incident->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan insiden: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
    {
        $incident = Insiden::with(['puskesmas', 'tahapan', 'status', 'kategoriInsiden', 'reporter'])
            ->find($id);
            
        if (!$incident) {
            $incident = new \Illuminate\Support\Fluent();
        }
        
        return view('reported-incidents.detail', [
            'incident' => $incident,
            'status' => collect(),
            'revisions' => collect(),
            'puskesmasList' => Puskesmas::all(),
            'jenisDokumenList' => JenisDokumen::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $incident = Insiden::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'tgl_kejadian' => 'nullable|date',
            'kategori_id' => 'nullable|integer|exists:kategori_insiden,id',
            'tahapan_id' => 'nullable|integer|exists:tahapan,id',
            'status_id' => 'nullable|integer|exists:status_insiden,id',
            'nama_korban' => 'nullable|string|max:255',
            'bagian' => 'nullable|string|max:255',
            'insiden' => 'nullable|string|max:500',
            'kronologis' => 'nullable|string',
            'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = array_filter($request->only([
                'tgl_kejadian', 'kategori_id', 'tahapan_id', 'status_id',
                'nama_korban', 'bagian', 'insiden', 'kronologis',
                'rencana_tindakan_koreksi', 'pelaksana_tindakan_koreksi', 
                'tgl_selesai_koreksi', 'verifikasi_hasil_koreksi',
                'verifikasi_tgl_koreksi', 'verifikasi_pelaksana_koreksi',
                'rencana_tindakan_korektif', 'pelaksana_tindakan_korektif',
                'tgl_selesai_korektif', 'verifikasi_hasil_korektif',
                'verifikasi_tgl_korektif', 'verifikasi_pelaksana_korektif'
            ]), function($value) {
                return $value !== null && $value !== '';
            });

            // Handle file uploads for dokumentasi
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPaths = [];
                foreach ($request->file('dokumentasi') as $file) {
                    $path = $file->store('incidents/dokumentasi', 'public');
                    $dokumentasiPaths[] = Storage::url($path);
                }
                
                // Append to existing dokumentasi
                $existingDocs = $incident->dokumentasi ? explode(',', $incident->dokumentasi) : [];
                $allDocs = array_merge($existingDocs, $dokumentasiPaths);
                $updateData['dokumentasi'] = implode(',', $allDocs);
            }

            $incident->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Data insiden berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    // API Methods for dropdown data
    public function getKategoriInsiden()
    {
        $kategori = KategoriInsiden::select('id', 'name')->get();
        return response()->json($kategori);
    }

    public function getTahapan()
    {
        $tahapan = Tahapan::select('id', 'tahapan')->get();
        return response()->json($tahapan);
    }

    public function getStatusInsiden()
    {
        $status = StatusInsiden::select('id', 'name')->get();
        return response()->json($status);
    }
}