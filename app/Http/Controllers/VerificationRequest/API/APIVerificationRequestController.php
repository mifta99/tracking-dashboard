<?php

namespace App\Http\Controllers\VerificationRequest\API;

use App\Http\Controllers\Controller;
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
     * Store a newly created verification request.
     */
    public function putDeliveryInformation(Request $request): JsonResponse
    {
       return response()->json(['message' => 'Index of verification requests']);
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