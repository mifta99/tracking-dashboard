<?php

namespace App\Http\Controllers\RaisedIssue;

use App\Http\Controllers\Controller;
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
        $data = Keluhan::all();
        
        return view('raised-issue.index', ['data' => $data]);
    }
}