<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class ImpressumController extends Controller
{
    /**
     * Display the Impressum (Legal Notice) page.
     */
    public function index()
    {
        $name = Setting::get('impressum_name', 'Privatperson');
        $email = Setting::get('impressum_email', '');
        $content = Setting::get('impressum_content', '');
        $enabled = Setting::get('impressum_enabled', '1');

        if ($enabled !== '1' && ! auth()->check()) {
            abort(404);
        }

        if (request()->ajax()) {
            return view('movies.partials.impressum', compact('name', 'email', 'content', 'enabled'));
        }

        return view('impressum', compact('name', 'email', 'content', 'enabled'));
    }
}
