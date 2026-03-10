<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'required|string|max:255',
            'items_per_page' => 'required|integer|min:5|max:100',
            'latest_films_count' => 'required|integer|min:5|max:50',
            'tmdb_api_key' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            $group = (str_starts_with($key, 'tmdb_')) ? 'tmdb' : 'general';
            Setting::set($key, $value, $group);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Einstellungen wurden gespeichert.');
    }
}
