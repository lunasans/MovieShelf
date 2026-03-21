<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ThemeController extends Controller
{
    /**
     * Save the selected theme to session and settings (if authenticated).
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string|max:50',
        ]);

        $theme = $validated['theme'];

        // Save to session for immediate UI feedback
        Session::put('theme', $theme);

        // If user is admin (or we want to save it globally), update setting
        if (auth()->check()) {
            Setting::set('theme', $theme, 'ui');
        }

        return response()->json([
            'success' => true,
            'theme' => $theme,
        ]);
    }
}
