<?php

namespace App\Http\Controllers\VerificationRequest\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\Revision;
use App\Models\UjiFungsi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class APIVerificationRequestController extends Controller
{
    /**
     * Display a listing of verification requests.
     */
    public function editBasicInformation(Request $request, string $id): JsonResponse
    {
        try {
            // Find the puskesmas
            $puskesmas = Puskesmas::find($id);
            if (!$puskesmas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data puskesmas tidak ditemukan'
                ], 404);
            }

            // Validate input - make most fields optional for partial updates
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'district_id' => 'sometimes|required|exists:districts,id',
                'province_id' => 'sometimes|nullable|exists:provinces,id',
                'regency_id' => 'sometimes|nullable|exists:regencies,id',
                'pic' => 'nullable|string|max:255',
                'no_hp' => 'nullable|string|max:255',
                'no_hp_alternatif' => 'nullable|string|max:255',
                'kepala' => 'nullable|string|max:255',
                'pic_dinkes_prov' => 'nullable|string|max:255',
                'pic_dinkes_kab' => 'nullable|string|max:255',
            ], [
                'name.required' => 'Nama puskesmas wajib diisi',
                'name.max' => 'Nama puskesmas maksimal 255 karakter',
                'district_id.required' => 'Kecamatan wajib dipilih',
                'district_id.exists' => 'Kecamatan yang dipilih tidak valid',
                'province_id.exists' => 'Provinsi yang dipilih tidak valid',
                'regency_id.exists' => 'Kabupaten/Kota yang dipilih tidak valid',
                'pic.max' => 'Nama PIC maksimal 255 karakter',
                'no_hp.max' => 'No. HP maksimal 255 karakter',
                'no_hp_alternatif.max' => 'No. HP Alternatif maksimal 255 karakter',
                'kepala.max' => 'Nama kepala puskesmas maksimal 255 karakter',
                'pic_dinkes_prov.max' => 'Nama PIC Dinkes Provinsi maksimal 255 karakter',
                'pic_dinkes_kab.max' => 'Nama PIC ADINKES maksimal 255 karakter',
            ]);

            // Only update fields that were provided
            $updateData = [];
            $updatedFields = [];

            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
                $updatedFields[] = 'nama puskesmas';
            }
            if (isset($validated['district_id'])) {
                $updateData['district_id'] = $validated['district_id'];
                $updatedFields[] = 'kecamatan';
            }
            if (array_key_exists('pic', $validated)) {
                $updateData['pic'] = $validated['pic'];
                $updatedFields[] = 'PIC puskesmas';
            }
            if (array_key_exists('no_hp', $validated)) {
                $updateData['no_hp'] = $validated['no_hp'];
                $updatedFields[] = 'No. HP puskesmas';
            }
            if (array_key_exists('no_hp_alternatif', $validated)) {
                $updateData['no_hp_alternatif'] = $validated['no_hp_alternatif'];
                $updatedFields[] = 'No. HP Alternatif puskesmas';
            }
            if (array_key_exists('kepala', $validated)) {
                $updateData['kepala'] = $validated['kepala'];
                $updatedFields[] = 'kepala puskesmas';
            }
            if (array_key_exists('pic_dinkes_prov', $validated)) {
                $updateData['pic_dinkes_prov'] = $validated['pic_dinkes_prov'];
                $updatedFields[] = 'PIC dinkes provinsi';
            }
            if (array_key_exists('pic_dinkes_kab', $validated)) {
                $updateData['pic_dinkes_kab'] = $validated['pic_dinkes_kab'];
                $updatedFields[] = 'PIC ADINKES';
            }

            // Update the puskesmas
            if (!empty($updateData)) {
                $puskesmas->update($updateData);
            }

            // Refresh the model to get updated data with relationships
            $puskesmas->refresh();
            $puskesmas->load('district.regency.province');

            // Prepare response data
            $responseData = [
                'name' => $puskesmas->name,
                'pic' => $puskesmas->pic,
                'no_hp' => $puskesmas->no_hp,
                'no_hp_alternatif' => $puskesmas->no_hp_alternatif,
                'kepala' => $puskesmas->kepala,
                'pic_dinkes_prov' => $puskesmas->pic_dinkes_prov,
                'pic_dinkes_kab' => $puskesmas->pic_dinkes_kab,
                'district_id' => $puskesmas->district_id,
            ];

            // Add location names if district was updated
            if (isset($updateData['district_id'])) {
                $responseData['district_name'] = optional($puskesmas->district)->name;
                $responseData['regency_name'] = optional(optional($puskesmas->district)->regency)->name;
                $responseData['province_name'] = optional(optional(optional($puskesmas->district)->regency)->province)->name;
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $responseData,
                'updated_fields' => $updatedFields
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data'
            ], 500);
        }
    }

   /**
     * Update delivery information for a puskesmas.
     */
    public function editDeliveryInformation(Request $request, string $id): JsonResponse
    {
        try {
            // Find the puskesmas
            $puskesmas = Puskesmas::with('pengiriman')->find($id);
            if (!$puskesmas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data puskesmas tidak ditemukan'
                ], 404);
            }

            // Validate input
            $validated = $request->validate([
                'tgl_pengiriman' => 'nullable|date',
                'eta' => 'nullable|date',
                'resi' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'tgl_diterima' => 'nullable|date',
                'nama_penerima' => 'nullable|string|max:255',
                'jabatan_penerima' => 'nullable|string|max:255',
                'instansi_penerima' => 'nullable|string|max:255',
                'nomor_penerima' => 'nullable|string|max:20',
                'link_tanda_terima' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
                'catatan' => 'nullable|string|max:1000',
            ], [
                'tgl_pengiriman.date' => 'Format tanggal pengiriman tidak valid',
                'eta.date' => 'Format tanggal ETA tidak valid',
                'resi.max' => 'Nomor resi maksimal 255 karakter',
                'tracking_link.url' => 'Format URL tracking tidak valid',
                'serial_number.max' => 'Serial number maksimal 255 karakter',
                'tgl_diterima.date' => 'Format tanggal diterima tidak valid',
                'nama_penerima.max' => 'Nama penerima maksimal 255 karakter',
                'jabatan_penerima.max' => 'Jabatan penerima maksimal 255 karakter',
                'instansi_penerima.max' => 'Instansi penerima maksimal 255 karakter',
                'nomor_penerima.max' => 'Nomor HP penerima maksimal 20 karakter',
                'link_tanda_terima.file' => 'File tanda terima tidak valid',
                'link_tanda_terima.mimes' => 'File tanda terima harus berformat PDF, JPG, JPEG, atau PNG',
                'link_tanda_terima.max' => 'Ukuran file tanda terima maksimal 5MB',
                'tahapan_id.exists' => 'Tahapan yang dipilih tidak valid',
                'catatan.max' => 'Catatan maksimal 1000 karakter',
            ]);

            // Handle equipment creation/update if serial number is provided
            if (!empty($validated['serial_number'])) {
                Equipment::firstOrCreate(
                    [
                        'serial_number' => $validated['serial_number'],
                        'puskesmas_id' => $puskesmas->id
                    ],
                    ['name' => null] // Can be updated later
                );
            }

            // Handle file upload
            $tandaTerimaPath = null;
            if ($request->hasFile('link_tanda_terima')) {
                $file = $request->file('link_tanda_terima');
                $tandaTerimaPath = $file->store('tanda-terima', 'public');
            }

            // Prepare update data
            $updateData = [];
            $updatedFields = [];

            foreach (['tgl_pengiriman', 'eta', 'resi', 'tracking_link', 'tgl_diterima', 'nama_penerima', 'jabatan_penerima', 'instansi_penerima', 'nomor_penerima', 'tahapan_id', 'catatan'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                    $updatedFields[] = str_replace('_', ' ', $field);
                }
            }

            // Serial number is handled separately in equipment table
            if (!empty($validated['serial_number'])) {
                $updatedFields[] = 'Serial Number';
            }

            // Add file path if uploaded
            if ($tandaTerimaPath) {
                $updateData['link_tanda_terima'] = $tandaTerimaPath;
                $updatedFields[] = 'tanda terima';
            }

            // Add updated_by
            $updateData['updated_by'] = auth()->id();

            // Auto-update tahapan_id based on field updates
            $this->updateTahapanId($puskesmas, $validated, 'pengiriman');

            // Get or create pengiriman record
            if ($puskesmas->pengiriman) {
                $puskesmas->pengiriman->update($updateData);
                $pengiriman = $puskesmas->pengiriman;
            } else {
                // Ensure we have required data for creating new record
                $createData = $updateData;
                $createData['puskesmas_id'] = $puskesmas->id;
                $createData['tahapan_id'] = 1;
                $createData['created_by'] = auth()->id();

                // Add default values if no data provided
                if (empty($createData) || count($createData) <= 3) { // Only has puskesmas_id, created_by, updated_by
                    $createData['verif_kemenkes'] = false;
                }

                $pengiriman = Pengiriman::create($createData);
            }

            // Refresh with relationships
            $pengiriman->load(['tahapan']);
            // Load equipment through puskesmas relationship
            $puskesmas->load('equipment');

            return response()->json([
                'success' => true,
                'message' => 'Data pengiriman berhasil diperbarui',
                'data' => [
                    'tgl_pengiriman' => $pengiriman->tgl_pengiriman ? $pengiriman->tgl_pengiriman->translatedFormat('d F Y') : null,
                    'eta' => $pengiriman->eta ? $pengiriman->eta->translatedFormat('d F Y') : null,
                    'resi' => $pengiriman->resi,
                    'tracking_link' => $pengiriman->tracking_link,
                    'serial_number' => $puskesmas->equipment->serial_number ?? null,
                    'tgl_diterima' => $pengiriman->tgl_diterima ? $pengiriman->tgl_diterima->translatedFormat('d F Y') : null,
                    'nama_penerima' => $pengiriman->nama_penerima,
                    'jabatan_penerima' => $pengiriman->jabatan_penerima,
                    'instansi_penerima' => $pengiriman->instansi_penerima,
                    'nomor_penerima' => $pengiriman->nomor_penerima,
                    'catatan' => $pengiriman->catatan,
                    'tahapan_name' => $pengiriman->tahapan->tahapan ?? null,
                ],
                'updated_fields' => $updatedFields
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Edit uji fungsi information for a puskesmas.
     */
    public function editUjiFungsiInformation(Request $request, string $id): JsonResponse
    {
        try {
            // Find the puskesmas
            $puskesmas = Puskesmas::with('ujiFungsi')->find($id);
            if (!$puskesmas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data puskesmas tidak ditemukan'
                ], 404);
            }

            // Validate input
            $validated = $request->validate([
                'tgl_instalasi' => 'nullable|date',
                'target_tgl_uji_fungsi' => 'nullable|date',
                'tgl_uji_fungsi' => 'nullable|date',
                'tgl_pelatihan' => 'nullable|date',
                'doc_instalasi' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
                'doc_uji_fungsi' => 'nullable|file|mimes:pdf|max:5120',
                'doc_pelatihan' => 'nullable|file|mimes:pdf|max:5120',
                'catatan' => 'nullable|string',
            ], [
                'tgl_instalasi.date' => 'Format tanggal instalasi tidak valid',
                'target_tgl_uji_fungsi.date' => 'Format target tanggal uji fungsi tidak valid',
                'tgl_uji_fungsi.date' => 'Format tanggal uji fungsi tidak valid',
                'tgl_pelatihan.date' => 'Format tanggal pelatihan tidak valid',
                'doc_instalasi.file' => 'File berita acara instalasi tidak valid',
                'doc_instalasi.mimes' => 'File berita acara instalasi harus berformat PDF',
                'doc_instalasi.max' => 'Ukuran file berita acara instalasi maksimal 5MB',
                'doc_uji_fungsi.file' => 'File berita acara uji fungsi tidak valid',
                'doc_uji_fungsi.mimes' => 'File berita acara uji fungsi harus berformat PDF',
                'doc_uji_fungsi.max' => 'Ukuran file berita acara uji fungsi maksimal 5MB',
                'doc_pelatihan.file' => 'File berita acara pelatihan tidak valid',
                'doc_pelatihan.mimes' => 'File berita acara pelatihan harus berformat PDF',
                'doc_pelatihan.max' => 'Ukuran file berita acara pelatihan maksimal 5MB',
            ]);

            // Handle file uploads and resolve active revisions
            $fileFields = ['doc_instalasi', 'doc_uji_fungsi', 'doc_pelatihan'];
            $resolvedRevisions = [];
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    // Create directory if it doesn't exist
                    $uploadPath = 'uploads/uji-fungsi';
                    $fullPath = storage_path('app/public/' . $uploadPath);
                    if (!file_exists($fullPath)) {
                        mkdir($fullPath, 0755, true);
                    }

                    // Generate unique filename
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Store file
                    $file->storeAs($uploadPath, $fileName, 'public');

                    // Store relative path
                    $validated[$field] = $uploadPath . '/' . $fileName;

                    // Map document fields to jenis_dokumen_id
                    $jenisDokumenMapping = [
                        'doc_instalasi' => 3,  // Instalasi
                        'doc_uji_fungsi' => 4, // Uji Fungsi
                        'doc_pelatihan' => 5   // Pelatihan
                    ];

                    // Resolve active revisions for this document type
                    if (isset($jenisDokumenMapping[$field])) {
                        $jenisDokumenId = $jenisDokumenMapping[$field];
                        $activeRevisions = Revision::where('puskesmas_id', $id)
                            ->where('jenis_dokumen_id', $jenisDokumenId)
                            ->where('is_resolved', 0)
                            ->get();

                        if ($activeRevisions->count() > 0) {
                            // Update each active revision to resolved (don't modify catatan)
                            foreach ($activeRevisions as $revision) {
                                $revision->update([
                                    'is_resolved' => 1,
                                    'resolved_at' => now(),
                                    'resolved_by' => auth()->id()
                                ]);
                            }

                            $resolvedRevisions[] = $field;
                        }
                    }
                }
            }

            // Prepare update data
            $updateData = [];
            $updatedFields = [];

            foreach (['tgl_instalasi', 'target_tgl_uji_fungsi', 'tgl_uji_fungsi', 'tgl_pelatihan', 'catatan', 'doc_instalasi', 'doc_uji_fungsi', 'doc_pelatihan'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                    $updatedFields[] = str_replace(['_', 'doc_'], [' ', 'dokumen '], $field);
                }
            }

            // Add updated_by
            $updateData['updated_by'] = auth()->id();

            // Auto-update tahapan_id based on field updates
            $this->updateTahapanId($puskesmas, $validated, 'uji_fungsi');

            // Get or create uji fungsi record
            if ($puskesmas->ujiFungsi) {
                $puskesmas->ujiFungsi->update($updateData);
                $ujiFungsi = $puskesmas->ujiFungsi;
            } else {
                // Ensure we have required data for creating new record
                $createData = $updateData;
                $createData['puskesmas_id'] = $puskesmas->id;
                $createData['created_by'] = auth()->id();

                // Add default values if no data provided
                if (empty($createData) || count($createData) <= 3) { // Only has puskesmas_id, created_by, updated_by
                    $createData['verif_kemenkes'] = false;
                }

                $ujiFungsi = UjiFungsi::create($createData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data uji fungsi berhasil diperbarui',
                'data' => [
                    'tgl_instalasi' => $ujiFungsi->tgl_instalasi ? $ujiFungsi->tgl_instalasi->translatedFormat('d F Y') : null,
                    'target_tgl_uji_fungsi' => $ujiFungsi->target_tgl_uji_fungsi ? $ujiFungsi->target_tgl_uji_fungsi->translatedFormat('d F Y') : null,
                    'tgl_uji_fungsi' => $ujiFungsi->tgl_uji_fungsi ? $ujiFungsi->tgl_uji_fungsi->translatedFormat('d F Y') : null,
                    'tgl_pelatihan' => $ujiFungsi->tgl_pelatihan ? $ujiFungsi->tgl_pelatihan->translatedFormat('d F Y') : null,
                    'doc_instalasi' => $ujiFungsi->doc_instalasi,
                    'doc_uji_fungsi' => $ujiFungsi->doc_uji_fungsi,
                    'doc_pelatihan' => $ujiFungsi->doc_pelatihan,
                    'catatan' => $ujiFungsi->catatan,
                ],
                'updated_fields' => $updatedFields
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit document information for a puskesmas.
     */
    public function editDocumentInformation(Request $request, string $id): JsonResponse
    {
        try {
            // Find the puskesmas
            $puskesmas = Puskesmas::with('document')->find($id);
            if (!$puskesmas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data puskesmas tidak ditemukan'
                ], 404);
            }

            // Validate input
            $validated = $request->validate([
                'basto' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
                'kalibrasi' => 'nullable|file|mimes:pdf|max:5120',
                'bast' => 'nullable|file|mimes:pdf|max:5120',
                'aspak' => 'nullable|file|mimes:pdf|max:5120',
                'update_aspak' => 'nullable|file|mimes:pdf|max:5120',
            ], [
                'basto.file' => 'File berita acara BASTO tidak valid',
                'basto.mimes' => 'File berita acara BASTO harus berformat PDF',
                'basto.max' => 'Ukuran file berita acara BASTO maksimal 5MB',
                'kalibrasi.file' => 'File berita acara kalibrasi tidak valid',
                'kalibrasi.mimes' => 'File berita acara kalibrasi harus berformat PDF',
                'kalibrasi.max' => 'Ukuran file berita acara kalibrasi maksimal 5MB',
                'bast.file' => 'File berita acara BAST tidak valid',
                'bast.mimes' => 'File berita acara BAST harus berformat PDF',
                'bast.max' => 'Ukuran file berita acara BAST maksimal 5MB',
                'aspak.file' => 'File berita acara ASPAK tidak valid',
                'aspak.mimes' => 'File berita acara ASPAK harus berformat PDF',
                'aspak.max' => 'Ukuran file berita acara ASPAK maksimal 5MB',
                'update_aspak.file' => 'File update ASPAK tidak valid',
                'update_aspak.mimes' => 'File update ASPAK harus berformat PDF',
                'update_aspak.max' => 'Ukuran file update ASPAK maksimal 5MB',
            ]);

            // Handle file uploads
            $fileFields = ['basto', 'kalibrasi', 'bast', 'aspak', 'update_aspak'];
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    // Create directory if it doesn't exist
                    $uploadPath = 'uploads/documents';
                    $fullPath = storage_path('app/public/' . $uploadPath);
                    if (!file_exists($fullPath)) {
                        mkdir($fullPath, 0755, true);
                    }

                    // Generate unique filename
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Store file
                    $file->storeAs($uploadPath, $fileName, 'public');

                    // Store relative path
                    $validated[$field] = $uploadPath . '/' . $fileName;
                }
            }

            // Prepare update data
            $updateData = [];
            $updatedFields = [];

            foreach ($fileFields as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                    $updatedFields[] = str_replace('_', ' ', $field);
                }
            }

            // Add updated_by
            $updateData['updated_by'] = auth()->id();

            // Auto-update tahapan_id based on field updates
            $this->updateTahapanId($puskesmas, $validated, 'documents');

            // Get or create document record
            if ($puskesmas->document) {
                $puskesmas->document->update($updateData);
                $document = $puskesmas->document;
            } else {
                // Ensure we have required data for creating new record
                $createData = $updateData;
                $createData['puskesmas_id'] = $puskesmas->id;
                $createData['created_by'] = auth()->id();

                // Add default values if no data provided
                if (empty($createData) || count($createData) <= 3) { // Only has puskesmas_id, created_by, updated_by
                    $createData['verif_kemenkes'] = false;
                }

                $document = Document::create($createData);
            }

            // Resolve revisions for uploaded documents
            $documentTypeMapping = [
                'kalibrasi' => 1,
                'bast' => 2,
                'basto' => 6,
                'aspak' => 7,
            ];

            foreach ($fileFields as $field) {
                if (array_key_exists($field, $validated) && isset($documentTypeMapping[$field])) {
                    // Document was uploaded, resolve any active revisions for this document type
                    Revision::where('puskesmas_id', $id)
                        ->where('jenis_dokumen_id', $documentTypeMapping[$field])
                        ->where('is_resolved', 0)
                        ->update([
                            'is_resolved' => 1,
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id()
                        ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data dokumen berhasil diperbarui',
                'data' => [
                    'basto' => $document->basto,
                    'kalibrasi' => $document->kalibrasi,
                    'bast' => $document->bast,
                    'aspak' => $document->aspak,
                    'update_aspak' => $document->update_aspak,
                ],
                'updated_fields' => $updatedFields
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-update tahapan_id in pengiriman table based on field updates
     * Only updates if the new tahapan_id is higher than the current one
     */
    private function updateTahapanId($puskesmas, $validated, $context)
    {
        // Get current tahapan_id from pengiriman record
        $currentTahapanId = 1; // Default
        if ($puskesmas->pengiriman) {
            $currentTahapanId = $puskesmas->pengiriman->tahapan_id ?? 1;
        }

        $newTahapanId = null;

        // Determine new tahapan_id based on context and updated fields
        switch ($context) {
            case 'pengiriman':
                // Check if resi field is being updated and has a value
                if (array_key_exists('resi', $validated) && !empty($validated['resi'])) {
                    $newTahapanId = 2;
                }
                break;

            case 'documents':
                // Check document fields in priority order (higher tahapan_id takes precedence)
                if (array_key_exists('aspak', $validated) && !empty($validated['aspak'])) {
                    $newTahapanId = 8; // aspak has highest priority
                } elseif (array_key_exists('basto', $validated) && !empty($validated['basto'])) {
                    $newTahapanId = 7; // basto
                } elseif (array_key_exists('bast', $validated) && !empty($validated['bast'])) {
                    $newTahapanId = 3; // bast
                }
                break;

            case 'uji_fungsi':
                // Check uji fungsi document fields in priority order
                if (array_key_exists('doc_pelatihan', $validated) && !empty($validated['doc_pelatihan'])) {
                    $newTahapanId = 6; // doc_pelatihan has highest priority in uji_fungsi
                } elseif (array_key_exists('doc_uji_fungsi', $validated) && !empty($validated['doc_uji_fungsi'])) {
                    $newTahapanId = 5; // doc_uji_fungsi
                } elseif (array_key_exists('doc_instalasi', $validated) && !empty($validated['doc_instalasi'])) {
                    $newTahapanId = 4; // doc_instalasi
                }
                break;
        }

        // Only update if new tahapan_id is higher than current
        if ($newTahapanId && $newTahapanId > $currentTahapanId) {
            // Get or create pengiriman record to update tahapan_id
            if ($puskesmas->pengiriman) {
                $puskesmas->pengiriman->update(['tahapan_id' => $newTahapanId]);
            } else {
                // Create pengiriman record with new tahapan_id
                Pengiriman::create([
                    'puskesmas_id' => $puskesmas->id,
                    'tahapan_id' => $newTahapanId,
                    'verif_kemenkes' => false,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            Log::info("Updated tahapan_id from {$currentTahapanId} to {$newTahapanId} for puskesmas {$puskesmas->id} in context {$context}");
        }
    }

    public function instalasiVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_instalasi' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $ujiFungsi = $puskesmas->ujiFungsi;

            // Create uji_fungsi record if it doesn't exist
            if (!$ujiFungsi) {
                $ujiFungsi = new UjiFungsi();
                $ujiFungsi->puskesmas_id = $id;
                $ujiFungsi->created_by = auth()->id();
                $ujiFungsi->save();

                // Reload the relationship
                $puskesmas->load('ujiFungsi');
                $ujiFungsi = $puskesmas->ujiFungsi;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_instalasi;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$ujiFungsi->doc_instalasi || empty($ujiFungsi->doc_instalasi))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara Instalasi belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $ujiFungsi->is_verified_instalasi = $isVerified;
            $ujiFungsi->verified_at_instalasi = $isVerified ? now() : null;
            $ujiFungsi->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 3) // Instalasi document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara Instalasi berhasil diverifikasi' :
                    'Verifikasi Berita Acara Instalasi berhasil dibatalkan',
                'data' => [
                    'is_verified' => $ujiFungsi->is_verified_instalasi,
                    'verified_at' => $ujiFungsi->verified_at_instalasi ?
                        $ujiFungsi->verified_at_instalasi->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ujiFungsiVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_uji_fungsi' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $ujiFungsi = $puskesmas->ujiFungsi;

            // Create uji_fungsi record if it doesn't exist
            if (!$ujiFungsi) {
                $ujiFungsi = new UjiFungsi();
                $ujiFungsi->puskesmas_id = $id;
                $ujiFungsi->created_by = auth()->id();
                $ujiFungsi->save();

                // Reload the relationship
                $puskesmas->load('ujiFungsi');
                $ujiFungsi = $puskesmas->ujiFungsi;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_uji_fungsi;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$ujiFungsi->doc_uji_fungsi || empty($ujiFungsi->doc_uji_fungsi))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara Uji Fungsi belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $ujiFungsi->is_verified_uji_fungsi = $isVerified;
            $ujiFungsi->verified_at_uji_fungsi = $isVerified ? now() : null;
            $ujiFungsi->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 4) // Uji Fungsi document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara Uji Fungsi berhasil diverifikasi' :
                    'Verifikasi Berita Acara Uji Fungsi berhasil dibatalkan',
                'data' => [
                    'is_verified' => $ujiFungsi->is_verified_uji_fungsi,
                    'verified_at' => $ujiFungsi->verified_at_uji_fungsi ?
                        $ujiFungsi->verified_at_uji_fungsi->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pelatihanVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_pelatihan' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $ujiFungsi = $puskesmas->ujiFungsi;

            // Create uji_fungsi record if it doesn't exist
            if (!$ujiFungsi) {
                $ujiFungsi = new UjiFungsi();
                $ujiFungsi->puskesmas_id = $id;
                $ujiFungsi->created_by = auth()->id();
                $ujiFungsi->save();

                // Reload the relationship
                $puskesmas->load('ujiFungsi');
                $ujiFungsi = $puskesmas->ujiFungsi;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_pelatihan;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$ujiFungsi->doc_pelatihan || empty($ujiFungsi->doc_pelatihan))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara Pelatihan Alat belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $ujiFungsi->is_verified_pelatihan = $isVerified;
            $ujiFungsi->verified_at_pelatihan = $isVerified ? now() : null;
            $ujiFungsi->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 5) // Pelatihan document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara Pelatihan Alat berhasil diverifikasi' :
                    'Verifikasi Berita Acara Pelatihan Alat berhasil dibatalkan',
                'data' => [
                    'is_verified' => $ujiFungsi->is_verified_pelatihan,
                    'verified_at' => $ujiFungsi->verified_at_pelatihan ?
                        $ujiFungsi->verified_at_pelatihan->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addRevision(Request $request, $id)
    {
        try {
            $request->validate([
                'jenis_dokumen_id' => 'required',
                'catatan' => 'required|string|max:1000'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);

            // Create revision record (adjust based on your revision table structure)
            $revision = new Revision();
            $revision->puskesmas_id = $id;
            $revision->jenis_dokumen_id = $request->jenis_dokumen_id;
            $revision->catatan = $request->catatan;
            $revision->revised_by = auth()->id();
            $revision->save();

            return response()->json([
                'success' => true,
                'message' => 'Catatan revisi berhasil disimpan',
                'data' => $revision
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified verification request.
     */
    public function show(string $id): JsonResponse
    {
       return response()->json(['message' => 'Index of verification requests']);
    }

    /**
     * Update the specified verification request.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Index of verification requests']);
    }

    public function kalibrasiVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_kalibrasi' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $document = $puskesmas->document;

            // Create document record if it doesn't exist
            if (!$document) {
                $document = new Document();
                $document->puskesmas_id = $id;
                $document->created_by = auth()->id();
                $document->save();

                // Reload the relationship
                $puskesmas->load('document');
                $document = $puskesmas->document;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_kalibrasi;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$document->kalibrasi || empty($document->kalibrasi))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara Kalibrasi belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $document->is_verified_kalibrasi = $isVerified;
            $document->verified_at_kalibrasi = $isVerified ? now() : null;
            $document->updated_by = auth()->id();
            $document->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 1) // Kalibrasi document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara Kalibrasi berhasil diverifikasi' :
                    'Verifikasi Berita Acara Kalibrasi berhasil dibatalkan',
                'data' => [
                    'is_verified' => $document->is_verified_kalibrasi,
                    'verified_at' => $document->verified_at_kalibrasi ?
                        $document->verified_at_kalibrasi->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bastVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_bast' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $document = $puskesmas->document;

            // Create document record if it doesn't exist
            if (!$document) {
                $document = new Document();
                $document->puskesmas_id = $id;
                $document->created_by = auth()->id();
                $document->save();

                // Reload the relationship
                $puskesmas->load('document');
                $document = $puskesmas->document;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_bast;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$document->bast || empty($document->bast))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara BAST belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $document->is_verified_bast = $isVerified;
            $document->verified_at_bast = $isVerified ? now() : null;
            $document->updated_by = auth()->id();
            $document->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 2) // BAST document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara BAST berhasil diverifikasi' :
                    'Verifikasi Berita Acara BAST berhasil dibatalkan',
                'data' => [
                    'is_verified' => $document->is_verified_bast,
                    'verified_at' => $document->verified_at_bast ?
                        $document->verified_at_bast->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bastoVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_basto' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $document = $puskesmas->document;

            // Create document record if it doesn't exist
            if (!$document) {
                $document = new Document();
                $document->puskesmas_id = $id;
                $document->created_by = auth()->id();
                $document->save();

                // Reload the relationship
                $puskesmas->load('document');
                $document = $puskesmas->document;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_basto;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$document->basto || empty($document->basto))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara BASTO belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $document->is_verified_basto = $isVerified;
            $document->verified_at_basto = $isVerified ? now() : null;
            $document->updated_by = auth()->id();
            $document->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 6) // BASTO document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara BASTO berhasil diverifikasi' :
                    'Verifikasi Berita Acara BASTO berhasil dibatalkan',
                'data' => [
                    'is_verified' => $document->is_verified_basto,
                    'verified_at' => $document->verified_at_basto ?
                        $document->verified_at_basto->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function aspakVerification(Request $request, $id)
    {
        try {
            $request->validate([
                'is_verified_aspak' => 'required'
            ]);

            $puskesmas = Puskesmas::findOrFail($id);
            $document = $puskesmas->document;

            // Create document record if it doesn't exist
            if (!$document) {
                $document = new Document();
                $document->puskesmas_id = $id;
                $document->created_by = auth()->id();
                $document->save();

                // Reload the relationship
                $puskesmas->load('document');
                $document = $puskesmas->document;
            }

            // Convert to boolean - handle various formats
            $isVerified = $request->is_verified_aspak;
            if (is_string($isVerified)) {
                $isVerified = in_array(strtolower($isVerified), ['true', '1', 'yes', 'on']);
            } else {
                $isVerified = (bool) $isVerified;
            }

            // Check if trying to verify but document is not uploaded
            if ($isVerified && (!$document->aspak || empty($document->aspak))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen Berita Acara ASPAK belum diunggah. Silakan unggah dokumen terlebih dahulu sebelum melakukan verifikasi.'
                ], 422);
            }

            $document->is_verified_aspak = $isVerified;
            $document->verified_at_aspak = $isVerified ? now() : null;
            $document->updated_by = auth()->id();
            $document->save();

            // If verifying (true), update any revisions for this document type (resolved or not)
            if ($isVerified) {
                Revision::where('puskesmas_id', $id)
                    ->where('jenis_dokumen_id', 7) // ASPAK document type
                    ->where('is_verified', 0)
                    ->update([
                        'is_verified' => 1,
                        'verified_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $isVerified ?
                    'Berita Acara ASPAK berhasil diverifikasi' :
                    'Verifikasi Berita Acara ASPAK berhasil dibatalkan',
                'data' => [
                    'is_verified' => $document->is_verified_aspak,
                    'verified_at' => $document->verified_at_aspak ?
                        $document->verified_at_aspak->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB' :
                        null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified verification request.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => 'Index of verification requests']);
    }
}
