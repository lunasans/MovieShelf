<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

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
            'default_view_mode' => 'required|string|in:grid,list',
            'theme' => 'required|string|max:50',
            'tmdb_api_key' => 'nullable|string|max:255',
            'impressum_name' => 'nullable|string|max:255',
            'impressum_email' => 'nullable|email|max:255',
            'impressum_content' => 'nullable|string',
            'impressum_enabled' => 'nullable|string',
            'signature_enabled' => 'nullable|string',
            'signature_film_count' => 'required|integer|min:1|max:20',
            'signature_film_source' => 'required|string|in:newest,newest_release,random',
            'signature_cache_time' => 'required|integer|min:0',
            'signature_show_title' => 'nullable|string',
            'signature_show_year' => 'nullable|string',
            'signature_show_rating' => 'nullable|string',
            'mail_mailer' => 'required|string|in:smtp,log,sendmail',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        // Handle checkboxes
        $checkboxes = ['impressum_enabled', 'signature_enabled', 'signature_show_title', 'signature_show_year', 'signature_show_rating'];
        foreach ($checkboxes as $checkbox) {
            $validated[$checkbox] = $request->has($checkbox) ? '1' : '0';
        }

        foreach ($validated as $key => $value) {
            $group = 'general';
            if (str_starts_with($key, 'tmdb_')) {
                $group = 'tmdb';
            } elseif ($key === 'theme') {
                $group = 'ui';
            } elseif (str_starts_with($key, 'impressum_')) {
                $group = 'impressum';
            } elseif (str_starts_with($key, 'signature_')) {
                $group = 'signature';
            } elseif (str_starts_with($key, 'mail_')) {
                $group = 'mail';
            }
            Setting::set($key, (string)$value, $group);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'SETTINGS_UPDATE',
            'details' => json_encode(['updated_fields' => array_keys($validated)]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Einstellungen wurden gespeichert.');
    }

    public function testMail(Request $request)
    {
        try {
            $to = $request->get('email', auth()->user()->email);
            
            Mail::send('emails.test', [], function ($message) use ($to) {
                $message->to($to)
                        ->subject('TMDb Film-Datenbank: Test-Email');
            });

            return response()->json(['success' => true, 'message' => 'Die HTML Test-Email wurde erfolgreich versendet an ' . $to]);
        } catch (\Exception $e) {
            Log::error('Mail Test failed: ' . $e->getMessage());
            
            // Provide a more user-friendly error message
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'authentication failed')) {
                $errorMessage = 'Authentifizierung fehlgeschlagen. Bitte prüfe Benutzername und Passwort.';
            } elseif (str_contains($errorMessage, 'Connection could not be established')) {
                $errorMessage = 'Verbindung zum SMTP-Server fehlgeschlagen. Bitte prüfe Host und Port.';
            }

            return response()->json(['success' => false, 'message' => 'Fehler: ' . $errorMessage], 500);
        }
    }
}
