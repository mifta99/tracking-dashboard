<?php

namespace App\Http\Controllers\ImportData;

use App\Http\Controllers\Controller;
use App\Imports\PuskesmasImport;
use App\Models\District;
use App\Models\Puskesmas;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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
    public function importPuskesmas(Request $request)
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
            $districts = District::pluck('id', 'name')->toArray();
            dd($districts);
            $districtsForMatching = District::select('id', 'name')->get()->toArray();
            foreach ($data as $item) {
                    $districtId = $districts[$item['kecamatan']] ?? null;
                    if (!$districtId) {
                        $districtId = $this->findClosestDistrictId($item['kecamatan'], $districtsForMatching);
                    }
                        Puskesmas::updateOrCreate(
                            [
                                'id' => $this->generatePuskesmasId($districtId, $item['nama_puskesmas'], Puskesmas::pluck('id')->toArray()),
                                'name' => $item['nama_puskesmas'],
                                'pic' => $item['pic_puskesmas_petugas_aspak'],
                                'kepala' => $item['kepala_puskesmas'],
                                'pic_dinkes_prov' => $item['pic_dinas_kesehatan_provinsi'],
                                'pic_dinkes_kab' => $item['pic_kabupaten_kota'],
                                'district_id' => $districtId,
                                'created_by' => $user_id,
                            ]
                        );
                    
                }
                
            $endTime = microtime(true);
            $duration = $endTime - $startTime;

            return redirect()->back()->with('success', 'File imported successfully! Waktu proses: ' . round($duration, 2) . ' detik');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
