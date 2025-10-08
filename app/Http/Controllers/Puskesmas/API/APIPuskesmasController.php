<?php

namespace App\Http\Controllers\Puskesmas\API;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Models\PuskesmasEmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;


class APIPuskesmasController extends Controller
{
    public function fetchData(Request $request)
    {
        try {            
            $query = Puskesmas::with(['district.regency.province', 'pengiriman']);
            
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
            
            // Filter by status (tahapan_id in pengiriman)
            if ($request->has('status') && $request->status !== '') {
                $statusId = $request->status;
                if ($statusId == '0') {
                    // Status "Belum Diproses" - no pengiriman record or tahapan_id is null
                    $query->where(function($q) {
                        $q->whereDoesntHave('pengiriman')
                          ->orWhereHas('pengiriman', function($subQ) {
                              $subQ->whereNull('tahapan_id');
                          });
                    });
                } else {
                    // Specific status - has pengiriman with matching tahapan_id
                    $query->whereHas('pengiriman', function($q) use ($statusId) {
                        $q->where('tahapan_id', $statusId);
                    });
                }
            }
            
            $data = $query->get();
            
            
            foreach ($data as $item) {
                $item->setAttribute('provinsi', $item->district->regency->province->name ?? 'N/A');
                $item->setAttribute('kabupaten_kota', $item->district->regency->name ?? 'N/A');
                $item->setAttribute('kecamatan', $item->district->name ?? 'N/A');
                $item->setAttribute('status_pengiriman', !empty($item->pengiriman) ? 1 : 0);
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
     * Store new puskesmas
     */
    public function store(Request $request)
    {
        try {
            
            
            // Validate request
            $validated = $request->validate([
                'id' => 'nullable|string|unique:puskesmas,id',
                'name' => 'required|string|max:255',
                'district_id' => 'required|exists:districts,id',
                'no_hp' => 'nullable|string|max:13',
                'no_hp_alternatif' => 'nullable|string|max:13',
                'pic' => 'nullable|string|max:255',
                'kepala' => 'nullable|string|max:255',
                'pic_dinkes_prov' => 'nullable|string|max:255',
                'pic_dinkes_kab' => 'nullable|string|max:255',
            ], [
                'id.unique' => 'ID puskesmas sudah digunakan',
                'name.required' => 'Nama puskesmas wajib diisi',
                'name.max' => 'Nama puskesmas maksimal 255 karakter',
                'no_hp.max' => 'Nomor HP maksimal 13 karakter',
                'no_hp_alternatif.max' => 'Nomor HP Alternatif maksimal 13 karakter',
                'district_id.required' => 'Kecamatan wajib dipilih',
                'district_id.exists' => 'Kecamatan yang dipilih tidak valid',
                'pic.max' => 'Nama PIC maksimal 255 karakter',
                'kepala.max' => 'Nama kepala puskesmas maksimal 255 karakter',
                'pic_dinkes_prov.max' => 'Nama PIC Dinkes Provinsi maksimal 255 karakter',
                'pic_dinkes_kab.max' => 'Nama PIC ADINKES maksimal 255 karakter',
            ]);
            
            // Check for duplicate name in the same district
            $existingPuskesmas = Puskesmas::where('name', $validated['name'])
                ->where('district_id', $validated['district_id'])
                ->first();
            
            if ($existingPuskesmas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puskesmas dengan nama tersebut sudah ada di kecamatan ini',
                    'errors' => [
                        'name' => ['Puskesmas dengan nama tersebut sudah ada di kecamatan ini']
                    ]
                ], 422);
            }
            

            // Create new puskesmas
            $puskesmas = Puskesmas::create([
                'id' => $validated['id'] ?? null,
                'name' => $validated['name'],
                'district_id' => $validated['district_id'],
                'pic' => $validated['pic'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'no_hp_alternatif' => $validated['no_hp_alternatif'] ?? null,
                'kepala' => $validated['kepala'] ?? null,
                'pic_dinkes_prov' => $validated['pic_dinkes_prov'] ?? null,
                'pic_dinkes_kab' => $validated['pic_dinkes_kab'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            
            Pengiriman::create([
                'puskesmas_id' => $puskesmas->id,
                'tahapan_id' => 1,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            // Load relationships for response
            $puskesmas->load(['district.regency.province']);
            
            Log::info('Puskesmas created successfully', ['id' => $puskesmas->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Puskesmas berhasil ditambahkan',
                'data' => $puskesmas
            ], 201);
            
        } catch (ValidationException $e) {
            Log::warning('Validation failed', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating puskesmas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Update basic editable fields of a Puskesmas
     * Fields: name, pic, kepala, pic_dinkes_prov, pic_dinkes_kab
     */
    public function updateBasic($id, Request $request)
    {
        try {
            $puskesmas = Puskesmas::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'pic' => 'nullable|string|max:255',
                'kepala' => 'nullable|string|max:255',
                'pic_dinkes_prov' => 'nullable|string|max:255',
                'pic_dinkes_kab' => 'nullable|string|max:255',
                'no_hp' => 'nullable|string|max:50',
                'no_hp_alternatif' => 'nullable|string|max:50',
            ], [
                'name.required' => 'Nama puskesmas wajib diisi',
                'name.max' => 'Nama puskesmas maksimal 255 karakter',
                'pic.max' => 'PIC Puskesmas maksimal 255 karakter',
                'kepala.max' => 'Kepala Puskesmas maksimal 255 karakter',
                'pic_dinkes_prov.max' => 'PIC Dinkes Provinsi maksimal 255 karakter',
                'pic_dinkes_kab.max' => 'PIC ADINKES maksimal 255 karakter',
                'no_hp.max' => 'No HP maksimal 50 karakter',
                'no_hp_alternatif.max' => 'No HP Alternatif maksimal 50 karakter',
            ]);

            // Only update provided keys
            $dirty = [];
            foreach (['name','pic','kepala','pic_dinkes_prov','pic_dinkes_kab','no_hp','no_hp_alternatif'] as $field) {
                if ($request->has($field)) {
                    $puskesmas->{$field} = $validated[$field] ?? null;
                    $dirty[] = $field;
                }
            }
            if(!empty($dirty)){
                $puskesmas->updated_by = auth()->id();
                $puskesmas->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'updated_fields' => $dirty,
                'data' => $puskesmas->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: '.$e->getMessage()
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

    public function testSmtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'to' => 'nullable|email',
                'code' => 'nullable|string|max:20',
                'verification_url' => 'nullable|url',
                'expires_at' => 'nullable|date',
                'name' => 'nullable|string|max:255',
                'app_name' => 'nullable|string|max:255',
                'mailer' => 'nullable|string',
            ]);

            $recipient = $validated['to'] ?? 'tpieceverfication@gmail.com';
            $mailerName = $validated['mailer'] ?? config('mail.default');

            $verificationCode = $validated['code'] ?? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $verificationUrl = $validated['verification_url'] ?? rtrim(config('app.url'), '/') . '/email/verify?code=' . urlencode($verificationCode);

            $expiresAtInput = $validated['expires_at'] ?? null;
            $expiresAt = $expiresAtInput
                ? Carbon::parse($expiresAtInput)->setTimezone('Asia/Jakarta')
                : Carbon::now('Asia/Jakarta')->addDay();

            $recipientName = $validated['name'] ?? null;
            $appName = $validated['app_name'] ?? null;

            Mail::mailer($mailerName)
                ->to($recipient)
                ->send(new EmailVerificationMail(
                    $verificationCode,
                    $verificationUrl,
                    $expiresAt,
                    $recipientName,
                    $appName
                ));

            $cfg = config("mail.mailers.$mailerName") ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Verification email dispatched.',
                'debug' => [
                    'to' => $recipient,
                    'mailer' => $mailerName,
                    'code' => $verificationCode,
                    'verification_url' => $verificationUrl,
                    'expires_at_iso' => $expiresAt ? $expiresAt->toIso8601String() : null,
                    'expires_at_local' => $expiresAt ? $expiresAt->copy()->locale('id')->translatedFormat('d F Y H:i') . ' WIB' : null,
                    'host' => $cfg['host'] ?? null,
                    'port' => $cfg['port'] ?? null,
                    'encryption' => $cfg['encryption'] ?? null,
                    'from' => config('mail.from.address'),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SMTP test failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'SMTP test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if email is available for use
     */
    

}
