<?php

namespace App\Http\Controllers\RaisedIssue;

use App\Http\Controllers\Controller;
use App\Models\KategoriKeluhan;
use App\Models\Keluhan;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
            'documentation' => 'nullable|array|max:5',
            'documentation.*' => 'file|mimes:jpeg,jpg,png|max:5120', // 5MB per file
        ], [
            'issue_subject.required' => 'Judul keluhan wajib diisi',
            'issue_subject.max' => 'Judul maksimal 255 karakter',
            'issue_description.required' => 'Deskripsi keluhan wajib diisi',
            'issue_description.max' => 'Deskripsi maksimal 1000 karakter',
            'documentation.max' => 'Maksimal 5 file dokumentasi',
            'documentation.*.mimes' => 'File harus berformat JPG atau PNG',
            'documentation.*.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            // Create the keluhan record with default values for admin-filled fields
            $keluhan = Keluhan::create([
                'puskesmas_id' => auth()->user()->puskesmas_id,
                'reported_subject' => $validated['issue_subject'],
                'reported_issue' => $validated['issue_description'],
                'kategori_id' => null,
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
                'message' => 'Terjadi kesalahan saat menyimpan keluhan. Silakan coba lagi.'
            ], 500);
        }
    }
    public function detail($id)
    {
        $keluhan = Keluhan::with(['puskesmas', 'kategoriKeluhan', 'statusKeluhan', 'reporter', 'dokumentasiKeluhan'])->find($id);
        if (!$keluhan) {
            return redirect()->route('raised-issue.index')->with('error', 'Keluhan tidak ditemukan.');
        }
        // Ensure puskesmas users can only view their own issues
        if (auth()->user()->role_id == 1 && $keluhan->puskesmas_id != auth()->user()->puskesmas_id) {
            return redirect()->route('raised-issue.index')->with('error', 'Akses ditolak.');
        }
        $kategoriKeluhan = KategoriKeluhan::all();
        return view('raised-issue.detail', ['issue' => $keluhan, 'kategoriKeluhan' => $kategoriKeluhan]);
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
        ], [
            'kategori_id.exists' => 'Kategori keluhan tidak valid',
            'proceed_date.date' => 'Format tanggal diproses tidak valid',
            'resolved_date.date' => 'Format tanggal selesai tidak valid',
            'action_taken.max' => 'Detail tindak lanjut maksimal 1000 karakter',
            'catatan.max' => 'Catatan maksimal 1000 karakter',
            'total_downtime.max' => 'Total downtime maksimal 255 karakter',
            'proceed_by.max' => 'Nama pemroses maksimal 255 karakter',
            'resolved_by.max' => 'Nama penyelesai maksimal 255 karakter',
        ]);

        try {
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
            'bukti_dokumentasi' => 'nullable|array|max:5',
            'bukti_dokumentasi.*' => 'file|mimes:jpeg,jpg,png|max:5120', // 5MB per file
        ], [
            'reported_subject.required' => 'Judul keluhan wajib diisi',
            'reported_subject.max' => 'Judul maksimal 255 karakter',
            'reported_issue.required' => 'Deskripsi keluhan wajib diisi',
            'reported_issue.max' => 'Deskripsi maksimal 1000 karakter',
            'bukti_dokumentasi.max' => 'Maksimal 5 file dokumentasi',
            'bukti_dokumentasi.*.mimes' => 'File harus berformat JPG atau PNG',
            'bukti_dokumentasi.*.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            // Update the keluhan record
            $keluhan->update([
                'reported_subject' => $validated['reported_subject'],
                'reported_issue' => $validated['reported_issue'],
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