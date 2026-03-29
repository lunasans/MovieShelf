<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function index()
    {
        return response()->json([
            'app_name' => config('app.name'),
            'version' => config('app.version'),
        ]);
    }
}
