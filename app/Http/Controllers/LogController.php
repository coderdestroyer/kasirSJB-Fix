<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
{
    // Ambil parameter pencarian dari request
    $search = $request->input('search');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $logId = $request->input('log_id');

    // Query logs dengan filter pencarian
    $logs = Log::when($search, function ($query, $search) {
        return $query->where('log_target', 'like', "%{$search}%")
                     ->orWhere('log_description', 'like', "%{$search}%");
    })->when($logId, function ($query, $logId) {
        return $query->where('log_id', $logId);
    })->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
        return $query->whereBetween('log_time', [$startDate, $endDate]);
    })->orderBy('log_time', 'desc') // Urutkan berdasarkan log_time secara menurun
      ->paginate(10);

    // Kembalikan view dengan data logs
    return view('logs.index', compact('logs'));
}


public function data(Request $request)
{
    $query = Log::query();

    // Pencarian berdasarkan parameter yang dikirim oleh DataTables
    if ($request->search['value']) {
        $search = $request->search['value'];
        $query->where('log_target', 'like', "%{$search}%")
              ->orWhere('log_description', 'like', "%{$search}%");
    }

    // Filter berdasarkan ID
    if ($request->log_id) {
        $query->where('log_id', $request->log_id);
    }

    // Filter berdasarkan rentang tanggal
    if ($request->start_date && $request->end_date) {
        $query->whereBetween('log_time', [$request->start_date, $request->end_date]);
    }

    // Urutkan berdasarkan log_time dari terbaru ke terlama
    $query->orderBy('log_time', 'desc');

    return datatables()->of($query)->make(true);
}

}
