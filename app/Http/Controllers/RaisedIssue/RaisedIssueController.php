<?php

namespace App\Http\Controllers\RaisedIssue;

use App\Http\Controllers\Controller;
use App\Models\KategoriKeluhan;
use App\Models\Keluhan;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RaisedIssueController extends Controller
{
    /**
     * Display a listing of raised issues.
     */
    public function index()
    {
        $data = Keluhan::query();
        if(auth()->user()->role_id == 1){ // Puskesmas
            $data = $data->where('puskesmas_id', auth()->user()->puskesmas_id);
        }
        $data = $data->get();
        $keluhanTipe = KategoriKeluhan::all();
        return view('raised-issue.index', ['data' => $data, 'keluhanTipe' => $keluhanTipe]);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'issue_subject' => 'required|string|max:255',
            'issue_description' => 'required|string',
            'priority' => 'required|exists:kategori_keluhan,id',
        ], [
            'issue_subject.required' => 'Judul wajib diisi',
            'issue_subject.max' => 'Judul maksimal 255 karakter',
            'issue_description.required' => 'Deskripsi wajib diisi',
            'issue_description.max' => 'Deskripsi maksimal 1000 karakter',
            'priority.required' => 'Prioritas wajib diisi',
            'priority.exists' => 'Prioritas tidak valid',
        ]);

        try {
            Keluhan::create([
                'puskesmas_id' => auth()->user()->puskesmas_id,
                'reported_subject' => $validated['issue_subject'],
                'reported_issue' => $validated['issue_description'],
                'kategori_id' => $validated['priority'],
                'reported_by' => auth()->user()->id,
                'status_id' => 1, 
                'reported_date'=> now(),
            ]);
            return response()->json(['message' => 'Keluhan berhasil ditambahkan'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan keluhan: ' . $e->getMessage()], 500);
        }
    }
    public function detail($id)
    {
        $keluhan = Keluhan::with(['puskesmas', 'kategoriKeluhan', 'statusKeluhan', 'reporter'])->find($id);
        if (!$keluhan) {
            return redirect()->route('raised-issue.index')->with('error', 'Keluhan tidak ditemukan.');
        }
        // Ensure puskesmas users can only view their own issues
        if (auth()->user()->role_id == 1 && $keluhan->puskesmas_id != auth()->user()->puskesmas_id) {
            return redirect()->route('raised-issue.index')->with('error', 'Akses ditolak.');
        }
        return view('raised-issue.detail', ['issue' => $keluhan]);
    }
}