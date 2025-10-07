<?php

namespace App\Http\Controllers\Incident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Revision;
use App\Models\Puskesmas;
use App\Models\JenisDokumen;

class IncidentController extends Controller
{
    public function index()
    {
        return view('reported-incident.index');
    }
    public function detail($id)
    {
        return view('reported-incidents.detail', ['incident' => collect(), 'status' => collect(), 'revisions' => collect(), 'puskesmasList' => Puskesmas::all(), 'jenisDokumenList' => JenisDokumen::all()]);
    }
}