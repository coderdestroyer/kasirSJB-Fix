<?php

namespace App\Http\Controllers;

use App\Models\Log;  // Pastikan model Log sudah diimport
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
{
    // Mengambil log dengan pagination
    $logs = Log::orderBy('created_at', 'desc')->paginate(10);
    return view('logs.index', compact('logs'));
}
}

