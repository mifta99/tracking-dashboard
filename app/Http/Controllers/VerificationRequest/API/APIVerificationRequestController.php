<?php

namespace App\Http\Controllers\VerificationRequest\API;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
                'eta' => 'nullable|integer|min:0',
                'resi' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'target_tgl' => 'nullable|date',
                'tgl_diterima' => 'nullable|date',
                'nama_penerima' => 'nullable|string|max:255',
                'jabatan_penerima' => 'nullable|string|max:255',
                'instansi_penerima' => 'nullable|string|max:255',
                'nomor_penerima' => 'nullable|string|max:20',
                'link_tanda_terima' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
                'catatan' => 'nullable|string|max:1000',
            ], [
                'tgl_pengiriman.date' => 'Format tanggal pengiriman tidak valid',
                'eta.integer' => 'ETA harus berupa angka',
                'eta.min' => 'ETA tidak boleh kurang dari 0',
                'resi.max' => 'Nomor resi maksimal 255 karakter',
                'tracking_link.url' => 'Format URL tracking tidak valid',
                'serial_number.max' => 'Serial number maksimal 255 karakter',
                'target_tgl.date' => 'Format tanggal target tidak valid',
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
            $equipmentId = null;
            if (!empty($validated['serial_number'])) {
                $equipment = Equipment::firstOrCreate(
                    ['serial_number' => $validated['serial_number']],
                    ['name' => null] // Can be updated later
                );
                $equipmentId = $equipment->id;
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

            foreach (['tgl_pengiriman', 'eta', 'resi', 'tracking_link', 'target_tgl', 'tgl_diterima', 'nama_penerima', 'jabatan_penerima', 'instansi_penerima', 'nomor_penerima', 'tahapan_id', 'catatan'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $updateData[$field] = $validated[$field];
                    $updatedFields[] = str_replace('_', ' ', $field);
                }
            }

            // Add equipment_id if we created/found equipment
            if ($equipmentId) {
                $updateData['equipment_id'] = $equipmentId;
                $updatedFields[] = 'Serial Number';
            }

            // Add file path if uploaded
            if ($tandaTerimaPath) {
                $updateData['link_tanda_terima'] = $tandaTerimaPath;
                $updatedFields[] = 'tanda terima';
            }

            // Add updated_by
            $updateData['updated_by'] = auth()->id();

            // Get or create pengiriman record
            if ($puskesmas->pengiriman) {
                $puskesmas->pengiriman->update($updateData);
                $pengiriman = $puskesmas->pengiriman;
            } else {
                $updateData['puskesmas_id'] = $puskesmas->id;
                $updateData['created_by'] = auth()->id();
                $pengiriman = Pengiriman::create($updateData);
            }

            // Refresh with relationships
            $pengiriman->load(['equipment', 'tahapan']);

            return response()->json([
                'success' => true,
                'message' => 'Data pengiriman berhasil diperbarui',
                'data' => [
                    'tgl_pengiriman' => $pengiriman->tgl_pengiriman ? $pengiriman->tgl_pengiriman->format('d F Y') : null,
                    'eta' => $pengiriman->eta,
                    'resi' => $pengiriman->resi,
                    'tracking_link' => $pengiriman->tracking_link,
                    'serial_number' => $pengiriman->equipment->serial_number ?? null,
                    'target_tgl' => $pengiriman->target_tgl ? $pengiriman->target_tgl->format('d F Y') : null,
                    'tgl_diterima' => $pengiriman->tgl_diterima ? $pengiriman->tgl_diterima->format('d F Y') : null,
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

    /**
     * Remove the specified verification request.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => 'Index of verification requests']);
    }
}
