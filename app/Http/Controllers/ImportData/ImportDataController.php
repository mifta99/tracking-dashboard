<?php

namespace App\Http\Controllers\ImportData;

use App\Exports\PuskesmasMasterExport;
use App\Http\Controllers\Controller;
use App\Imports\PuskesmasImport;
use App\Models\District;
use App\Models\Equipment;
use App\Models\Pengiriman;
use App\Models\Puskesmas;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ImportDataController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    function index()
    {
        return view('import-data.index');
    }
    function generatePuskesmasId($districtId, $namaPuskesmas, $existingIds = []) {
    $words = explode(' ', strtoupper(trim($namaPuskesmas)));
    $abbr = '';
    foreach ($words as $w) {
        $abbr .= substr($w, 0, 1);
    }
    $baseId = $districtId . strtolower($abbr);
    $newId = $baseId;
    $counter = 1;
    while (in_array($newId, $existingIds)) {
        $newId = $baseId . $counter;
        $counter++;
    }

    return $newId;
}
    function findClosestDistrictId($kecamatan, $districts) {
        $bestMatchId = null;
        $shortestDistance = -1;

        foreach ($districts as $district) {
            $lev = levenshtein(strtolower($kecamatan), strtolower($district['name']));

            if ($lev === 0) {
                return $district['id'];
            }

            if ($lev <= $shortestDistance || $shortestDistance < 0) {
                $bestMatchId = $district['id'];
                $shortestDistance = $lev;
            }
        }

        return $bestMatchId;
    }

    function importDataKemenkes($request){
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('excel_file');
        
        try {
            $startTime = microtime(true);

            $import = new PuskesmasImport();
            Excel::import($import, $file);
            $user_id = Auth::user()->id;
            $data = $import->getData();

            // Build a lookup map for exact matches and an array for fuzzy matching
            $query = DB::table('districts')
                ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                ->select(DB::raw("CONCAT(regencies.name, '-', districts.name) as regency_district"), 'districts.id');

            $districtMap = $query->pluck('id', 'regency_district'); // key => id for direct lookup
            $districtsList = $query->get()->map(function ($d) {
                return ['name' => $d->regency_district, 'id' => $d->id];
            })->toArray(); // array of ['name' => ..., 'id' => ...] for fuzzy matching

            foreach ($data as $item) {
                $key = ($item['kabupaten_kota'] ?? '') . '-' . ($item['kecamatan'] ?? '');
                $districtId = $districtMap[$key] ?? null;

                if (!$districtId) {
                    $districtId = $this->findClosestDistrictId($key, $districtsList);
                }
              
                // If still no district id found, skip this row
                if (!$districtId) {
                    continue;
                }

                // Check if puskesmas already exists with same name and district
                $existingPuskesmas = Puskesmas::where('id', $item['id_puskesmas'] ?? null)->first();
                if ($existingPuskesmas) {
                    // Update existing puskesmas
                    $existingPuskesmas->update([
                        'pic' => $item['pic_puskesmas'] ?? null,
                        'alamat' => $item['alamat'] ?? null,
                        'kepala' => $item['kepala_puskesmas'] ?? null,
                        'pic_dinkes_prov' => $item['pic_dinas_kesehatan_provinsi'] ?? null,
                        'pic_dinkes_kab' => $item['pic_kabupaten_kota'] ?? null,
                        'no_hp' => $item['no_hp'] ?? null,
                        'no_hp_alternatif' => $item['no_hp_alternatif'] ?? null,
                        'created_by' => $user_id,
                    ]);
                } else {
                    $puskesmasId = Puskesmas::create([
                        'id' => $item['id_puskesmas'],
                        'name' => ucwords(strtolower($item['nama_puskesmas'] ?? '')) ?? null,
                        'pic' => $item['pic_puskesmas'] ?? null,
                        'kepala' => $item['kepala_puskesmas'] ?? null,
                        'alamat' => $item['alamat'] ?? null,
                        'pic_dinkes_prov' => $item['pic_dinas_kesehatan_provinsi'] ?? null,
                        'pic_dinkes_kab' => $item['pic_kabupaten_kota'] ?? null,
                        'district_id' => $districtId,
                        'no_hp' => $item['no_hp'] ?? null,
                        'no_hp_alternatif' => $item['no_hp_alternatif'] ?? null,
                        'created_by' => $user_id,
                    ]);
                    if(Pengiriman::where('puskesmas_id', $puskesmasId->id)->first()) continue;
                    Pengiriman::create([
                        'puskesmas_id' => $puskesmasId->id,
                        'tahapan_id' => 1,
                        'created_by' => $user_id,
                    ]);
                }
            }
                
            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            return redirect()->back()->with('success', 'File imported successfully! Waktu proses: ' . round($duration, 2) . ' detik');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

        function importDataEndo($request)
        {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls,csv'
            ]);

            $file = $request->file('excel_file');
            
            try {
                $startTime = microtime(true);

                $import = new PuskesmasImport();
                Excel::import($import, $file);
                $user_id = Auth::user()->id;
                $data = $import->getData();

                // Helper function untuk parsing tanggal
                $parseDate = function ($value) {
                    if (empty($value)) return null;
                    try {
                        if (is_numeric($value)) {
                            return Date::excelToDateTimeObject($value);
                        }
                        return Carbon::createFromFormat('d-m-Y', trim($value));
                    } catch (\Exception $e) {
                        return null;
                    }
                };

                // Ambil mapping district
                $query = DB::table('districts')
                    ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                    ->select(DB::raw("CONCAT(regencies.name, '-', districts.name) as regency_district"), 'districts.id');

                $districtMap = $query->pluck('id', 'regency_district');
                $districtsList = $query->get()->map(function ($d) {
                    return ['name' => $d->regency_district, 'id' => $d->id];
                })->toArray(); 

                foreach ($data as $item) {
                    $key = ($item['kabupaten_kota'] ?? '') . '-' . ($item['kecamatan'] ?? '');
                    $districtId = $districtMap[$key] ?? null;

                    if (!$districtId) {
                        $districtId = $this->findClosestDistrictId($key, $districtsList);
                    }
                    if (!$districtId) continue;

                    // Skip kalau semua field kosong
                    $shippingFields = [
                        'tanggal_pengiriman', 'eta', 'nomor_resi', 'serial_number', 
                        'catatan', 'tanggal_diterima', 'nama_penerima', 'jabatan_penerima', 
                        'instansi_penerima', 'nomor_penerima', 'tanggal_instalasi', 'target_tanggal_uji_fungsi', 
                        'tanggal_uji_fungsi', 'tanggal_pelatihan'
                    ];
                    $allFieldsNull = true;
                    foreach ($shippingFields as $field) {
                        if (!empty($item[$field])) {
                            $allFieldsNull = false;
                            break;
                        }
                    }
                    if ($allFieldsNull) continue;

                    // Cari Puskesmas
                    $id = trim((string) ($item['id_puskesmas'] ?? ''));
                    $existingPuskesmas = Puskesmas::where('id', $id)->first();

                    $existingPengiriman = null;
                    $existingEquipment = null;
                    if ($existingPuskesmas) {
                        $existingPengiriman = Pengiriman::where('puskesmas_id', $existingPuskesmas->id)->first();
                        $existingEquipment = Equipment::where('puskesmas_id', $existingPuskesmas->id)->first();
                    }

                    // Update jika sudah ada Puskesmas + Pengiriman
                    if ($existingPuskesmas && $existingPengiriman) {

                        $updateData = [];
                        $normalizeDate = function ($value) {
                            if (empty($value)) {
                                return null;
                            }

                            if ($value instanceof \DateTimeInterface) {
                                return Carbon::instance($value)->format('Y-m-d');
                            }

                            try {
                                return Carbon::parse($value)->format('Y-m-d');
                            } catch (\Exception $e) {
                                return null;
                            }
                        };

                        $setIfChanged = function (string $field, $newValue, bool $isDate = false) use (&$updateData, $existingPengiriman, $normalizeDate) {
                            if ($newValue === null || $newValue === '') {
                                return;
                            }

                            if ($isDate) {
                                if ($newValue instanceof \DateTimeInterface && !($newValue instanceof Carbon)) {
                                    $newValue = Carbon::instance($newValue);
                                }

                                $currentValue = $normalizeDate(data_get($existingPengiriman, $field));
                                $incomingValue = $normalizeDate($newValue);

                                if ($incomingValue === null || $currentValue === $incomingValue) {
                                    return;
                                }

                                $updateData[$field] = $newValue;
                                return;
                            }

                            $currentValue = data_get($existingPengiriman, $field);
                            if (is_string($currentValue)) {
                                $currentValue = trim($currentValue);
                            }

                            $incomingValue = $newValue;
                            if (is_string($incomingValue)) {
                                $incomingValue = trim($incomingValue);
                            }

                            if ($currentValue === $incomingValue) {
                                return;
                            }

                            $updateData[$field] = is_string($newValue) ? trim($newValue) : $newValue;
                        };

                        if (!empty($item['tanggal_pengiriman'])) {
                            $setIfChanged('tgl_pengiriman', $parseDate($item['tanggal_pengiriman']), true);
                        }
                        if (!empty($item['eta'])) {
                            $setIfChanged('eta', $parseDate($item['eta']), true);
                        }
                        if (!empty($item['nomor_resi'])) {
                            $setIfChanged('resi', $item['nomor_resi']);
                        }
                        if ($existingPuskesmas && !empty($item['serial_number'])) {
                            $serial = (string) trim($item['serial_number']);
                            if ($existingEquipment) {
                                $currentSerial = trim((string) $existingEquipment->serial_number);
                                if ($currentSerial !== $serial) {
                                    $existingEquipment->update(['serial_number' => $serial]);
                                }
                            } else {
                                $existingEquipment = Equipment::create([
                                    'serial_number' => $serial,
                                    'puskesmas_id'  => $existingPuskesmas->id
                                ]);
                            }
                        }
                        if (!empty($item['catatan'])) {
                            $setIfChanged('catatan', $item['catatan']);
                        }
                        if (!empty($item['tanggal_diterima'])) {
                            $setIfChanged('tgl_diterima', $parseDate($item['tanggal_diterima']), true);
                        }
                        if (!empty($item['nama_penerima'])) {
                            $setIfChanged('nama_penerima', $item['nama_penerima']);
                        }
                        if (!empty($item['jabatan_penerima'])) {
                            $setIfChanged('jabatan_penerima', $item['jabatan_penerima']);
                        }
                        if (!empty($item['instansi_penerima'])) {
                            $setIfChanged('instansi_penerima', $item['instansi_penerima']);
                        }
                        if (!empty($item['nomor_penerima'])) {
                            $setIfChanged('nomor_penerima', $item['nomor_penerima']);
                        }
                        if (!empty($item['tanggal_instalasi'])) {
                            $setIfChanged('tanggal_instalasi', $parseDate($item['tanggal_instalasi']), true);
                        }
                        if (!empty($item['target_tanggal_uji_fungsi'])) {
                            $setIfChanged('target_tanggal_uji_fungsi', $parseDate($item['target_tanggal_uji_fungsi']), true);
                        }
                        if (!empty($item['tanggal_uji_fungsi'])) {
                            $setIfChanged('tanggal_uji_fungsi', $parseDate($item['tanggal_uji_fungsi']), true);
                        }
                        if (!empty($item['tanggal_pelatihan'])) {
                            $setIfChanged('tanggal_pelatihan', $parseDate($item['tanggal_pelatihan']), true);
                        }

                        if (!empty($updateData)) {
                            $existingPengiriman->update($updateData);
                        }
                    } 
                    // Jika Puskesmas ada tapi Pengiriman belum ada
                    else if ($existingPuskesmas) {
                        $serial = !empty($item['serial_number']) ? (string) trim($item['serial_number']) : null;
                        if ($serial) {
                            if ($existingEquipment) {
                                $currentSerial = trim((string) $existingEquipment->serial_number);
                                if ($currentSerial !== $serial) {
                                    $existingEquipment->update(['serial_number' => $serial]);
                                }
                            } else {
                                $existingEquipment = Equipment::create([
                                    'serial_number' => $serial,
                                    'puskesmas_id'  => $existingPuskesmas->id
                                ]);
                            }
                        }

                        Pengiriman::create([
                            'puskesmas_id' => $existingPuskesmas->id,
                            'tgl_pengiriman' => $parseDate($item['tanggal_pengiriman']),
                            'eta' => $parseDate($item['eta']),
                            'resi' => $item['nomor_resi'] ?? null,
                            'catatan' => $item['catatan'] ?? null,
                            'tgl_diterima' => $parseDate($item['tanggal_diterima']),
                            'nama_penerima' => $item['nama_penerima'] ?? null,
                            'tahapan_id' => 1,
                            'instansi_penerima' => $item['instansi_penerima'] ?? null,
                            'jabatan_penerima' => $item['jabatan_penerima'] ?? null,
                            'nomor_penerima' => $item['nomor_penerima'] ?? null,
                            'created_by' => $user_id,
                        ]);
                    }
                }
                    
                $endTime = microtime(true);
                $duration = $endTime - $startTime;

                return redirect()->back()->with('success', 'File imported successfully! Waktu proses: ' . round($duration, 2) . ' detik');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }
    public function importPuskesmas(Request $request)
    {
        switch (auth()->user()->role_id) {
            case 2:
                 set_time_limit(0);
                return $this->importDataKemenkes($request);

            case 3:
                return $this->importDataEndo($request);
            default:
                return redirect()->back()->with('error', 'Invalid action specified.');
        }
    }
    function downloadExcel(Request $request)
    {
        $additionalColumns = [];
        
        // Get additional columns selection for Endo role
        if (auth()->user()->role_id == 3 && $request->has('additional_columns')) {
            $additionalColumns = $request->input('additional_columns', []);
        }
        
        $export = new PuskesmasMasterExport(auth()->user()->role_id, $additionalColumns);
        return Excel::download($export, auth()->user()->role_id == 2 ? 'Kemenkes - Template Import Puskesmas.xlsx' : 'Endo - Template Import Puskesmas.xlsx');
    }
    public function importDataKilat($name)
    {
        set_time_limit(0); 

        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'GEMINI_API_KEY belum di-set pada environment'
            ];
        }

        $prompt = 'Pisahkan data ini menjadi nama, jabatan, dan nomor HP dalam format JSON:' . 
                $name . 
                '(Hanya Format Jsonnya Saja)';

        $client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com/',
            'verify' => false,
            'timeout' => 20
        ]);

        try {
            $response = $client->post("v1beta/models/gemini-2.5-flash-lite:generateContent?key={$apiKey}", [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $parsed = null;
            $cleanText = is_string($text) ? trim($text) : null;

            if (is_string($cleanText)) {
                if (preg_match('/^```[a-zA-Z]*\s*(.*?)```$/s', $cleanText, $m)) {
                    $cleanText = $m[1];
                }
                $parsed = json_decode($cleanText, true);
                if ($parsed === null) {
                    $start = strpos($cleanText, '{');
                    $end = strrpos($cleanText, '}');
                    if ($start !== false && $end !== false && $end > $start) {
                        $jsonFragment = substr($cleanText, $start, $end - $start + 1);
                        $parsed = json_decode($jsonFragment, true);
                    }
                }
            }

            $dataOut = null;
            if (is_array($parsed)) {
                $get = function(array $arr, array $keys) {
                    foreach ($keys as $k) {
                        if (array_key_exists($k, $arr) && $arr[$k] !== '' && $arr[$k] !== null) {
                            return $arr[$k];
                        }
                    }
                    return null;
                };
                $dataOut = [
                    'nama' => $get($parsed, ['nama', 'name']),
                    'jabatan' => $get($parsed, ['jabatan', 'position', 'title']),
                    'nomor_hp' => $get($parsed, ['nomor_hp', 'no_hp', 'nomor', 'hp', 'phone', 'telepon', 'phone_number'])
                ];
            }

            // return array biasa, bukan JSON response
            return [
                'success' => true,
                'text' => $text,
                'data' => $dataOut,
                'nama' => $dataOut['nama'] ?? null,
                'jabatan' => $dataOut['jabatan'] ?? null,
                'nomor_hp' => $dataOut['nomor_hp'] ?? null,
                'raw_json' => $cleanText,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    function ApiShowKilatAPI(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('excel_file');
        $import = new PuskesmasImport();
        Excel::import($import, $file);
        $data = $import->getData();
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    function importDataKilatAPI(Request $request)
    {

        $name = $request->input('name');
        $idPuskesmas = $request->input('id_puskesmas');
        if($name == null) return response()->json([
            'success' => false,
            'message' => 'Name is required'
        ], 400);
        $result = $this->importDataKilat($name);

        $existingPuskesmas = Puskesmas::where('id', $idPuskesmas ?? null)->first();

            if($result['success']){
            $existingPuskesmas->update([
                'pic' => $result['nama'] ?? null,
                'jabatan_pic' => $result['jabatan'] ??  null,
                'no_hp' => $result['nomor_hp'] ?? null,
            ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui Puskesmas: ' . ($result['message'] ?? 'Unknown error')
                ], 500);
            }
            
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'puskesmas_id' => $idPuskesmas,
                'data' => [
                    'nama' => $result['nama'] ?? null,
                    'jabatan' => $result['jabatan'] ?? null,
                    'nomor_hp' => $result['nomor_hp'] ?? null,
                ],
                'raw_response' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Unknown error'
            ], 500);
        }
    }
}
