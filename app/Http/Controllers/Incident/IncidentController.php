<?php

namespace App\Http\Controllers\Incident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Revision;
use App\Models\Puskesmas;
use App\Models\JenisDokumen;
use App\Models\Insiden;
use App\Models\DokumentasiInsiden;
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

    public function store(Request $request, $puskesmas_id)
    {
        // Validate that puskesmas_id from route exists
        $puskesmasValidator = Validator::make(['puskesmas_id' => $puskesmas_id], [
            'puskesmas_id' => 'required|exists:puskesmas,id',
        ]);

        if ($puskesmasValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Puskesmas tidak valid.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'tgl_kejadian' => 'required|date',
            'kategori_id' => 'required|integer|exists:kategori_insidens,id',
            'nama_korban' => 'required|string|max:255',
            'bagian' => 'required|string|max:255',
            'insiden' => 'required|string|max:500',
            'kronologis' => 'required|string',
            'tindakan' => 'nullable|string',
            'tgl_selesai' => 'nullable|date',
            'tahapan_id' => 'required|integer|exists:tahapan,id',
            'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ], [
            'tgl_kejadian.required' => 'Tanggal kejadian harus diisi.',
            'kategori_id.required' => 'Kategori insiden harus dipilih.',
            'nama_korban.required' => 'Nama korban harus diisi.',
            'bagian.required' => 'Bagian/unit harus diisi.',
            'insiden.required' => 'Judul insiden harus diisi.',
            'kronologis.required' => 'Kronologis kejadian harus diisi.',
            'tahapan_id.required' => 'Tahapan harus dipilih.',
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

            // Auto-set status to "selesai" (2) if tgl_selesai is provided, otherwise default (1)
            $status_id = $request->filled('tgl_selesai') ? 2 : 1;

            // Create incident
            $incident = Insiden::create([
                'puskesmas_id' => $puskesmas_id,
                'tahapan_id' => $request->tahapan_id,
                'status_id' => $status_id,
                'kategori_id' => $request->kategori_id,
                'tgl_kejadian' => $request->tgl_kejadian,
                'nama_korban' => $request->nama_korban,
                'bagian' => $request->bagian,
                'insiden' => $request->insiden,
                'kronologis' => $request->kronologis,
                'tindakan' => $request->tindakan,
                'tgl_selesai' => $request->tgl_selesai,
                'reported_by' => $user->id,
            ]);

            // Handle file uploads if present
            if ($request->hasFile('dokumentasi')) {
                foreach ($request->file('dokumentasi') as $index => $file) {
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('insiden-documentation', $filename, 'public');

                    // Create documentation record
                    $incident->dokumentasiInsiden()->create([
                        'link_foto' => $path,
                    ]);
                }
            }

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
            'tgl_kejadian' => 'required|date',
            'kategori_id' => 'required|integer|exists:kategori_insidens,id',
            'tahapan_id' => 'required|integer|exists:tahapan,id',
            'status_id' => 'nullable|integer|exists:status_insiden,id',
            'nama_korban' => 'required|string|max:255',
            'bagian' => 'required|string|max:255',
            'insiden' => 'required|string|max:500',
            'kronologis' => 'required|string',
            'tindakan' => 'nullable|string',
            'tgl_selesai' => 'nullable|date',
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
                'nama_korban', 'bagian', 'insiden', 'kronologis', 'tindakan', 'tgl_selesai',
                'rencana_tindakan_koreksi', 'pelaksana_tindakan_koreksi',
                'tgl_selesai_koreksi', 'verifikasi_hasil_koreksi',
                'verifikasi_tgl_koreksi', 'verifikasi_pelaksana_koreksi',
                'rencana_tindakan_korektif', 'pelaksana_tindakan_korektif',
                'tgl_selesai_korektif', 'verifikasi_hasil_korektif',
                'verifikasi_tgl_korektif', 'verifikasi_pelaksana_korektif'
            ]), function($value) {
                return $value !== null && $value !== '';
            });

            // Auto-update status to "selesai" (2) when tgl_selesai is provided
            if ($request->filled('tgl_selesai')) {
                $updateData['status_id'] = 2; // Status "Selesai"
            }

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
        $kategori = KategoriInsiden::select('id', 'kategori')->get();
        return response()->json($kategori);
    }

    public function getTahapan()
    {
        $tahapan = Tahapan::select('id', 'tahapan')->get();
        return response()->json($tahapan);
    }

    public function getStatusInsiden()
    {
        $status = StatusInsiden::select('id', 'status')->get();
        return response()->json($status);
    }

    public function fetchData(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Insiden::with(['puskesmas.district.regency.province', 'tahapan', 'status', 'kategoriInsiden', 'reporter']);

            // Filter by puskesmas if provided or by user role
            if ($request->has('puskesmas_id') && $request->puskesmas_id) {
                $query->where('puskesmas_id', $request->puskesmas_id);
            } elseif ($user->role_id === 1) { // Puskesmas user
                $query->where('puskesmas_id', $user->puskesmas_id);
            }

            $incidents = $query->latest('tgl_kejadian')->get();

            $formattedData = $incidents->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'province_name' => $incident->puskesmas->district->regency->province->name ?? '-',
                    'regency_name' => $incident->puskesmas->district->regency->name ?? '-',
                    'district_name' => $incident->puskesmas->district->name ?? '-',
                    'puskesmas_name' => $incident->puskesmas->name ?? '-',
                    'tgl_kejadian' => $incident->tgl_kejadian ? \Carbon\Carbon::parse($incident->tgl_kejadian)->translatedFormat('d M Y') : '-',
                    'insiden' => $incident->insiden ?? '-',
                    'tahapan' => $incident->tahapan->tahapan ?? '-',
                    'kategori_insiden' => $incident->kategoriInsiden->kategori ?? '-',
                    'status' => $incident->status->status ?? '-',
                    'reporter' => $incident->reporter->name ?? '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data insiden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get total incident count for menu badge
     * Count incidents that are not completed (status_id != 2)
     */
    public static function getTotalIncidentCount()
    {
        try {
            // Count incidents that are not resolved (status_id != 2, where 2 = selesai)
            return Insiden::where('status_id', '!=', 2)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
