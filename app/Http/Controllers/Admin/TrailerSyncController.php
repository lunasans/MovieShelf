<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrailerSyncRun;
use Illuminate\Http\Request;

class TrailerSyncController extends Controller
{
    /**
     * Display a listing of sync runs.
     */
    public function index()
    {
        $runs = TrailerSyncRun::orderBy('started_at', 'desc')->paginate(20);
        return view('admin.movies.sync_logs.index', compact('runs'));
    }

    /**
     * Display the specified sync run logs.
     */
    public function show(TrailerSyncRun $run)
    {
        $logs = $run->logs()->with('movie')->get();
        return view('admin.movies.sync_logs.show', compact('run', 'logs'));
    }
}
