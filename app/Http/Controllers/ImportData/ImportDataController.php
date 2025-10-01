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
                $existingPuskesmas = Puskesmas::where('name', $item['nama_puskesmas'] ?? null)
                    ->where('district_id', $districtId)
                    ->first();

                if ($existingPuskesmas) {
                    // Update existing puskesmas
                    $existingPuskesmas->update([
                        'pic' => $item['pic_puskesmas_petugas_aspak'] ?? null,
                        'kepala' => $item['kepala_puskesmas'] ?? null,
                        'pic_dinkes_prov' => $item['pic_dinas_kesehatan_provinsi'] ?? null,
                        'pic_dinkes_kab' => $item['pic_kabupaten_kota'] ?? null,
                        'created_by' => $user_id,
                    ]);
                } else {
                    // Create new puskesmas
                    $newId = $this->generatePuskesmasId(
                        $districtId,
                        $item['nama_puskesmas'] ?? '',
                        Puskesmas::pluck('id')->toArray()
                    );

                    $puskesmasId = Puskesmas::create([
                        'id' => $newId,
                        'name' => $item['nama_puskesmas'] ?? null,
                        'pic' => $item['pic_puskesmas_petugas_aspak'] ?? null,
                        'kepala' => $item['kepala_puskesmas'] ?? null,
                        'pic_dinkes_prov' => $item['pic_dinas_kesehatan_provinsi'] ?? null,
                        'pic_dinkes_kab' => $item['pic_kabupaten_kota'] ?? null,
                        'district_id' => $districtId,
                        'created_by' => $user_id,
                    ]);
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

        function importDataEndo($request){
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
           

            $query = DB::table('districts')
                ->join('regencies', 'districts.regency_id', '=', 'regencies.id')
                ->select(DB::raw("CONCAT(regencies.name, '-', districts.name) as regency_district"), 'districts.id');

            $districtMap = $query->pluck('id', 'regency_district'); // key => id for direct lookup
            $districtsList = $query->get()->map(function ($d) {
                return ['name' => $d->regency_district, 'id' => $d->id];
            })->toArray(); 

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
                $shippingFields = [
                    'tanggal_pengiriman', 'eta', 'resi', 'serial_number', 'target_tgl', 
                    'catatan', 'tgl_diterima', 'nama_penerima', 'jabatan_penerima', 
                    'instansi_penerima', 'nomor_penerima'
                ];

                $allFieldsNull = true;
                foreach ($shippingFields as $field) {
                    if (!empty($item[$field]) && $item[$field] !== null) {
                        $allFieldsNull = false;
                        break;
                    }
                }

                if ($allFieldsNull) {
                    continue;
                }
                // Check if puskesmas already exists with same name and district
                $existingPuskesmas = Puskesmas::where('name', $item['nama_puskesmas'] ?? null)
                    ->where('district_id', $districtId)
                    ->first();
                $existingPengiriman = Pengiriman::where('puskesmas_id', $existingPuskesmas->id)->first();
                if ($existingPuskesmas && $existingPengiriman) {
                    $equipment = Equipment::where('id', $existingPengiriman->equipment_id)->first();
                    if(!empty($item['serial_number']) && $item['serial_number'] != null){
                        if ($equipment) {
                            $equipment->serial_number = $item['serial_number'];
                            $equipment->save();
                        } else {
                            $equipment = Equipment::create([
                                'serial_number' => $item['serial_number']
                            ]);
                        }
                    }
                    $existingPengiriman->update([
                        'tgl_pengiriman' => Date::excelToDateTimeObject($item['tanggal_pengiriman']) ?? null,
                        'eta' => $item['eta'] ?? null,
                        'resi' => $item['resi'] ?? null,
                        'equipment_id' => $equipment ? $equipment->id : null,
                        'target_tgl' => $item['target_tgl'] ?? null,
                        'catatan' => $item['catatan'] ?? null,
                        'tahapan_id' => $existingPengiriman->tahapan_id ?? 1,
                        'tgl_diterima' => $item['tgl_diterima'] ?? null,
                        'nama_penerima' => $item['nama_penerima'] ?? null,
                        'instansi_penerima' => $item['instansi_penerima'] ?? null,
                    ]);
                } else if($existingPuskesmas){
                    $equipment = null;
                    if(!empty($item['serial_number']) && $item['serial_number'] != null){
                        $equipment = Equipment::create([
                            'serial_number' => $item['serial_number'],
                        ]);
                    }
                    if(!empty($item['serial_number']) || $item['serial_number'] != null){
                        $equipment = Equipment::create([
                            'serial_number' => $item['serial_number'] ?? null,
                        ]);
                    }

                    Pengiriman::create([
                        'puskesmas_id' => $existingPuskesmas->id,
                        'tgl_pengiriman' => Date::excelToDateTimeObject($item['tanggal_pengiriman']) ?? null,
                        'eta' => $item['eta'] ?? null,
                        'resi' => $item['resi'] ?? null,
                        'equipment_id' => $equipment ? $equipment->id : null,
                        'target_tgl' => $item['target_tgl'] ?? null,
                        'catatan' => $item['catatan'] ?? null,
                        'tgl_diterima' => $item['tgl_diterima'] ?? null,
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
