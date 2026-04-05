<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the SaaS landing page.
     */
    public function index()
    {
        return view('landing');
    }
}
