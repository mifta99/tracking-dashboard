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
                        'name' => $item['nama_puskesmas'] ?? null,
                        'pic' => $item['pic_puskesmas'] ?? null,
                        'kepala' => $item['kepala_puskesmas'] ?? null,
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
                    if ($existingPuskesmas) {
                        $existingPengiriman = Pengiriman::where('puskesmas_id', $existingPuskesmas->id)->first();
                    }

                    // Update jika sudah ada Puskesmas + Pengiriman
                    if ($existingPuskesmas && $existingPengiriman) {
                        $equipment = optional($existingPengiriman->equipment)->serial_number;
                        $updateData = [];

                        if (!empty($item['tanggal_pengiriman'])) {
                            $updateData['tgl_pengiriman'] = $parseDate($item['tanggal_pengiriman']);
                        }
                        if (!empty($item['eta'])) {
                            $updateData['eta'] = $parseDate($item['eta']);
                        }
                        if (!empty($item['nomor_resi'])) {
                            $updateData['resi'] = $item['nomor_resi'];
                        }
                        if ($existingPuskesmas && !empty($item['serial_number'])) {
                            $serial = (string) trim($item['serial_number']);
                            $equipment = Equipment::firstOrCreate([
                                'serial_number' => $serial,
                                'puskesmas_id'  => $existingPuskesmas->id
                            ]);
                        }
                        if (!empty($item['catatan'])) {
                            $updateData['catatan'] = $item['catatan'];
                        }
                        if (!empty($item['tanggal_diterima'])) {
                            $updateData['tgl_diterima'] = $parseDate($item['tanggal_diterima']);
                        }
                        if (!empty($item['nama_penerima'])) {
                            $updateData['nama_penerima'] = $item['nama_penerima'];
                        }
                        if (!empty($item['jabatan_penerima'])) {
                            $updateData['jabatan_penerima'] = $item['jabatan_penerima'];
                        }
                        if (!empty($item['instansi_penerima'])) {
                            $updateData['instansi_penerima'] = $item['instansi_penerima'];
                        }
                        if (!empty($item['nomor_penerima'])) {
                            $updateData['nomor_penerima'] = $item['nomor_penerima'];
                        }
                        if (!empty($item['tanggal_instalasi'])) {
                            $updateData['tanggal_instalasi'] = $parseDate($item['tanggal_instalasi']);
                        }
                        if (!empty($item['target_tanggal_uji_fungsi'])) {
                            $updateData['target_tanggal_uji_fungsi'] = $parseDate($item['target_tanggal_uji_fungsi']);
                        }
                        if (!empty($item['tanggal_uji_fungsi'])) {
                            $updateData['tanggal_uji_fungsi'] = $parseDate($item['tanggal_uji_fungsi']);
                        }
                        if (!empty($item['tanggal_pelatihan'])) {
                            $updateData['tanggal_pelatihan'] = $parseDate($item['tanggal_pelatihan']);
                        }

                        if (!empty($updateData)) {
                            $existingPengiriman->update($updateData);
                        }
                    } 
                    // Jika Puskesmas ada tapi Pengiriman belum ada
                    else if ($existingPuskesmas) {
                        $serial = !empty($item['serial_number']) ? (string) trim($item['serial_number']) : null;
                        if ($serial) {
                            Equipment::firstOrCreate([
                                'serial_number' => $serial,
                                'puskesmas_id'  => $existingPuskesmas->id
                            ]);
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
}
