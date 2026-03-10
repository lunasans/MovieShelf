<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Actor;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'totalMovies' => Movie::count(),
            'totalActors' => Actor::count(),
            'totalRuntime' => Movie::sum('runtime'),
            'collectionTypes' => Movie::selectRaw('collection_type, count(*) as count')->groupBy('collection_type')->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
