<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the SaaS landing page.
     */
    public function index()
    {
        $faqs = \App\Models\Faq::where('is_active', true)->orderBy('sort_order')->get();
        return view('central.landing', compact('faqs'));
    }
}
