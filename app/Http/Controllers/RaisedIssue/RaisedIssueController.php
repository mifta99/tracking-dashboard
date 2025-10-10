<?php

namespace App\Http\Controllers\RaisedIssue;

use App\Http\Controllers\Controller;
use App\Models\KategoriKeluhan;
use App\Models\Keluhan;
use App\Models\OpsiKeluhan;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RaisedIssueController extends Controller
{
    /**
     * Display a listing of raised issues.
     */
    public function index()
    {
        $keluhanTipe = KategoriKeluhan::all();
        return view('raised-issue.index', ['keluhanTipe' => $keluhanTipe]);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'issue_subject' => 'required|string|max:255',
            'issue_description' => 'required|string|max:1000',
            'opsi_keluhan_id' => 'required|exists:opsi_keluhan,id',
            'kategori_id' => 'nullable|exists:kategori_keluhan,id',
            'reported_name' => 'required|string|max:255',
            'reported_hp' => 'required|string|max:20',
            'documentation' => 'required|array|min:1|max:5',
            'documentation.*' => 'file|mimes:jpeg,jpg,png|max:5120', // 5MB per file
        ], [
            'issue_subject.required' => 'Ringkasan keluhan wajib diisi',
            'issue_subject.max' => 'Ringkasan maksimal 255 karakter',
            'issue_description.required' => 'Deskripsi keluhan wajib diisi',
            'issue_description.max' => 'Deskripsi maksimal 1000 karakter',
            'opsi_keluhan_id.required' => 'Opsi keluhan wajib dipilih',
            'opsi_keluhan_id.exists' => 'Opsi keluhan tidak valid',
            'kategori_id.exists' => 'Kategori keluhan tidak valid',
            'reported_name.required' => 'Nama pelapor wajib diisi',
            'reported_name.max' => 'Nama pelapor maksimal 255 karakter',
            'reported_hp.required' => 'Nomor HP pelapor wajib diisi',
            'reported_hp.max' => 'Nomor HP pelapor maksimal 20 karakter',
            'documentation.required' => 'Dokumentasi wajib diunggah',
            'documentation.min' => 'Minimal 1 file dokumentasi harus diunggah',
            'documentation.max' => 'Maksimal 5 file dokumentasi',
            'documentation.*.mimes' => 'File harus berformat JPG atau PNG',
            'documentation.*.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            // Get kategori_id from selected opsi_keluhan if not provided directly
            $kategoriId = $validated['kategori_id'] ?? null;
            if (!$kategoriId && $validated['opsi_keluhan_id']) {
                $opsiKeluhan = OpsiKeluhan::find($validated['opsi_keluhan_id']);
                $kategoriId = $opsiKeluhan ? $opsiKeluhan->kategori_keluhan_id : null;
            }

            // Create the keluhan record with default values for admin-filled fields
            $keluhan = Keluhan::create([
                'puskesmas_id' => auth()->user()->puskesmas_id,
                'reported_subject' => $validated['issue_subject'],
                'reported_issue' => $validated['issue_description'],
                'opsi_keluhan_id' => $validated['opsi_keluhan_id'],
                'kategori_id' => $kategoriId,
                'reported_name' => $validated['reported_name'],
                'reported_hp' => $validated['reported_hp'],
                'reported_by' => auth()->user()->id,
                'status_id' => 1, // Default status: new/baru
                'reported_date' => now(),
                'total_downtime' => null, // Will be filled by admin
            ]);

            // Handle file uploads if present
            if ($request->hasFile('documentation')) {
                foreach ($request->file('documentation') as $index => $file) {
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('keluhan-documentation', $filename, 'public');

                    // Create documentation record with only the required field
                    $keluhan->dokumentasiKeluhan()->create([
                        'link_foto' => $path,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Keluhan berhasil dilaporkan dan akan segera ditindaklanjuti.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error storing keluhan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function detail($id)
    {
        $keluhan = Keluhan::with(['puskesmas', 'kategoriKeluhan', 'statusKeluhan', 'reporter', 'dokumentasiKeluhan', 'opsiKeluhan'])->find($id);
        if (!$keluhan) {
            return redirect()->route('raised-issue.index')->with('error', 'Keluhan tidak ditemukan.');
        }
        // Ensure puskesmas users can only view their own issues
        if (auth()->user()->role_id == 1 && $keluhan->puskesmas_id != auth()->user()->puskesmas_id) {
            return redirect()->route('raised-issue.index')->with('error', 'Akses ditolak.');
        }
        $kategoriKeluhan = KategoriKeluhan::all();
        $opsiKeluhan = OpsiKeluhan::with('kategoriKeluhan')->orderBy('opsi', 'asc')->get();
        return view('raised-issue.detail', ['issue' => $keluhan, 'kategoriKeluhan' => $kategoriKeluhan, 'opsiKeluhan' => $opsiKeluhan]);
    }

    /**
     * Update tindak lanjut information (only accessible by endo users)
     */
    public function updateTindakLanjut(Request $request, $id): JsonResponse
    {
        // Check if user is endo
        if (auth()->user()->role->role_name !== 'endo') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya pengguna endo yang dapat mengedit tindak lanjut.'
            ], 403);
        }

        $keluhan = Keluhan::find($id);
        if (!$keluhan) {
            return response()->json([
                'success' => false,
                'message' => 'Keluhan tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'total_downtime' => 'nullable|string|max:255',
            'action_taken' => 'nullable|string|max:1000',
            'catatan' => 'nullable|string|max:1000',
            'kategori_id' => 'nullable|exists:kategori_keluhan,id',
            'proceed_by' => 'nullable|string|max:255',
            'proceed_date' => 'nullable|date',
            'resolved_by' => 'nullable|string|max:255',
            'resolved_date' => 'nullable|date',
            'doc_selesai' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:5120',
        ], [
            'kategori_id.exists' => 'Kategori keluhan tidak valid',
            'proceed_date.date' => 'Format tanggal diproses tidak valid',
            'resolved_date.date' => 'Format tanggal selesai tidak valid',
            'action_taken.max' => 'Detail tindak lanjut maksimal 1000 karakter',
            'catatan.max' => 'Catatan maksimal 1000 karakter',
            'total_downtime.max' => 'Total downtime maksimal 255 karakter',
            'proceed_by.max' => 'Nama pemroses maksimal 255 karakter',
            'resolved_by.max' => 'Nama penyelesai maksimal 255 karakter',
            'doc_selesai.file' => 'Dokumen penyelesaian harus berupa file',
            'doc_selesai.mimes' => 'Dokumen penyelesaian harus berformat: jpeg, jpg, png, pdf, doc, docx',
            'doc_selesai.max' => 'Dokumen penyelesaian maksimal 5MB',
        ]);

        try {
            // Handle file upload if present
            $docSelesaiPath = $keluhan->doc_selesai; // Keep existing document path
            if ($request->hasFile('doc_selesai')) {
                // Delete old file if it exists
                if ($keluhan->doc_selesai && Storage::disk('public')->exists($keluhan->doc_selesai)) {
                    Storage::disk('public')->delete($keluhan->doc_selesai);
                }

                // Store new file
                $docSelesaiPath = $request->file('doc_selesai')->store('keluhan/dokumen_selesai', 'public');
            }

            // Determine status automatically based on filled fields
            $status_id = 1; // Default: Baru

            if ($validated['resolved_by'] && $validated['resolved_date']) {
                $status_id = 3; // Selesai
            } elseif ($validated['proceed_by'] || $validated['proceed_date']) {
                $status_id = 2; // Proses
            }

            // Update the keluhan record
            $keluhan->update([
                'total_downtime' => $validated['total_downtime'],
                'action_taken' => $validated['action_taken'],
                'catatan' => $validated['catatan'],
                'kategori_id' => $validated['kategori_id'],
                'proceed_by' => $validated['proceed_by'],
                'proceed_date' => $validated['proceed_date'],
                'resolved_by' => $validated['resolved_by'],
                'resolved_date' => $validated['resolved_date'],
                'status_id' => $status_id,
                'doc_selesai' => $docSelesaiPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tindak lanjut berhasil diperbarui.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating tindak lanjut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui tindak lanjut. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Update laporan keluhan (only accessible by puskesmas users and only when status is 'Baru')
     */
    public function updateLaporan(Request $request, $id): JsonResponse
    {
        // Check if user is puskesmas
        if (auth()->user()->role->role_name !== 'puskesmas') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya pengguna puskesmas yang dapat mengedit laporan keluhan.'
            ], 403);
        }

        $keluhan = Keluhan::find($id);
        if (!$keluhan) {
            return response()->json([
                'success' => false,
                'message' => 'Keluhan tidak ditemukan.'
            ], 404);
        }

        // Check if keluhan belongs to the user's puskesmas
        if ($keluhan->puskesmas_id != auth()->user()->puskesmas_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda hanya dapat mengedit keluhan dari puskesmas Anda.'
            ], 403);
        }

        // Check if status is still 'Baru' (status_id = 1)
        if ($keluhan->status_id != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Keluhan hanya dapat diedit jika status masih "Baru".'
            ], 403);
        }

        $validated = $request->validate([
            'reported_subject' => 'required|string|max:255',
            'reported_issue' => 'required|string|max:1000',
            'opsi_keluhan_id' => 'required|exists:opsi_keluhan,id',
            'kategori_id' => 'nullable|exists:kategori_keluhan,id',
            'reported_name' => 'required|string|max:255',
            'reported_hp' => 'required|string|max:20',
            'bukti_dokumentasi' => 'nullable|array|max:5',
            'bukti_dokumentasi.*' => 'file|mimes:jpeg,jpg,png|max:5120', // 5MB per file
        ], [
            'reported_subject.required' => 'Ringkasan keluhan wajib diisi',
            'reported_subject.max' => 'Ringkasan maksimal 255 karakter',
            'reported_issue.required' => 'Deskripsi keluhan wajib diisi',
            'reported_issue.max' => 'Deskripsi maksimal 1000 karakter',
            'opsi_keluhan_id.required' => 'Opsi keluhan wajib dipilih',
            'opsi_keluhan_id.exists' => 'Opsi keluhan tidak valid',
            'kategori_id.exists' => 'Kategori keluhan tidak valid',
            'reported_name.required' => 'Nama pelapor wajib diisi',
            'reported_name.max' => 'Nama pelapor maksimal 255 karakter',
            'reported_hp.required' => 'Nomor HP pelapor wajib diisi',
            'reported_hp.max' => 'Nomor HP pelapor maksimal 20 karakter',
            'bukti_dokumentasi.max' => 'Maksimal 5 file dokumentasi',
            'bukti_dokumentasi.*.mimes' => 'File harus berformat JPG atau PNG',
            'bukti_dokumentasi.*.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            // Get kategori_id from selected opsi_keluhan if not provided directly
            $kategoriId = $validated['kategori_id'] ?? null;
            if (!$kategoriId && $validated['opsi_keluhan_id']) {
                $opsiKeluhan = OpsiKeluhan::find($validated['opsi_keluhan_id']);
                $kategoriId = $opsiKeluhan ? $opsiKeluhan->kategori_keluhan_id : null;
            }

            // Update the keluhan record
            $keluhan->update([
                'reported_subject' => $validated['reported_subject'],
                'reported_issue' => $validated['reported_issue'],
                'opsi_keluhan_id' => $validated['opsi_keluhan_id'],
                'kategori_id' => $kategoriId,
                'reported_name' => $validated['reported_name'],
                'reported_hp' => $validated['reported_hp'],
            ]);

            // Handle file uploads if present - replace existing documentation
            if ($request->hasFile('bukti_dokumentasi')) {
                // Delete old documentation files and records
                foreach ($keluhan->dokumentasiKeluhan as $oldDoc) {
                    // Delete file from storage if it exists
                    $fullPath = storage_path('app/public/' . $oldDoc->link_foto);
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                    $oldDoc->delete();
                }

                // Upload new files
                foreach ($request->file('bukti_dokumentasi') as $index => $file) {
                    $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('keluhan-documentation', $filename, 'public');

                    // Create new documentation record
                    $keluhan->dokumentasiKeluhan()->create([
                        'link_foto' => $path,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan keluhan berhasil diperbarui.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating laporan keluhan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui laporan keluhan. Silakan coba lagi.'
            ], 500);
        }
    }
}
