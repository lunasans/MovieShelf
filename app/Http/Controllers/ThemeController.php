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

        // Das Theme-Setting gilt fuer die gesamte Instanz (auch fuer Gaeste).
        // Es darf daher nur von Admins geschrieben werden – sonst koennte jeder
        // angemeldete Nutzer das Erscheinungsbild fuer alle umstellen.
        if (auth()->check() && auth()->user()->is_admin) {
            Setting::set('theme', $theme, 'ui');
        }

        return response()->json([
            'success' => true,
            'theme' => $theme,
        ]);
    }
}
