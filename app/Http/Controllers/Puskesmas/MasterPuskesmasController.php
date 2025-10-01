<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Models\Puskesmas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterPuskesmasController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            // Load puskesmas data with relationships
            $data = Puskesmas::with(['district.regency.province'])->get();
            
            return view('puskesmas.index', ['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error loading puskesmas data: ' . $e->getMessage());
            return view('puskesmas.index', ['data' => collect()]);
        }
    }
    
}
