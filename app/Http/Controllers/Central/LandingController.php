<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\LandingPage;
use App\Models\LandingScreenshot;
use App\Models\Tenant;

class LandingController extends Controller
{
    public function index()
    {
        $faqs        = Faq::where('is_active', true)->orderBy('sort_order')->get();
        $screenshots = LandingScreenshot::where('is_active', true)->orderBy('sort_order')->get();
        $navPages    = LandingPage::where('is_active', true)->where('show_in_nav', true)->orderBy('sort_order')->get();

        return view('central.landing', compact('faqs', 'screenshots', 'navPages'));
    }

    public function page(string $slug)
    {
        $page = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $navPages = LandingPage::where('is_active', true)->where('show_in_nav', true)->orderBy('sort_order')->get();

        return view('central.page', compact('page', 'navPages'));
    }

    public function discover()
    {
        $tenants = Tenant::whereNotNull('activated_at')
            ->latest('activated_at')
            ->limit(10)
            ->get();

        return view('central.discover', compact('tenants'));
    }
}
