<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            'boxset_quick_view_style' => 'required|string|in:island,modal',
            'theme' => 'required|string|max:50',
            'tmdb_api_key' => 'nullable|string|max:255',
            'impressum_name' => 'nullable|string|max:255',
            'impressum_email' => 'nullable|email|max:255',
            'impressum_content' => 'nullable|string',
            'impressum_enabled' => 'nullable|string',
            'cookie_banner_enabled' => 'nullable|string',
            'cookie_banner_text' => 'nullable|string',
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
            'ignored_update_files' => 'nullable|string',
        ]);

        $this->handleCheckboxes($request, $validated);
        $this->sanitizeHtml($validated);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value, $this->getSettingGroup($key));
        }

        $this->handleSignatureCache($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'SETTINGS_UPDATE',
            'details' => json_encode(['updated_fields' => array_keys($validated)]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Einstellungen wurden gespeichert.');
    }

    protected function handleCheckboxes(Request $request, array &$validated)
    {
        $checkboxes = ['impressum_enabled', 'cookie_banner_enabled', 'signature_enabled', 'signature_show_title', 'signature_show_year', 'signature_show_rating', 'migration_enabled', 'telemetry_enabled'];
        foreach ($checkboxes as $checkbox) {
            $validated[$checkbox] = $request->has($checkbox) ? '1' : '0';
        }
    }

    protected function sanitizeHtml(array &$validated)
    {
        if (isset($validated['impressum_content'])) {
            $validated['impressum_content'] = strip_tags($validated['impressum_content'], '<a><p><br><strong><em><ul><li><h3><h4><h5><h6>');
        }
        if (isset($validated['cookie_banner_text'])) {
            $validated['cookie_banner_text'] = strip_tags($validated['cookie_banner_text'], '<a><strong><em>');
        }
    }

    public function getSettingGroup(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'tmdb_') => 'tmdb',
            $key === 'theme' => 'ui',
            str_starts_with($key, 'impressum_') => 'impressum',
            str_starts_with($key, 'signature_') => 'signature',
            str_starts_with($key, 'mail_') => 'mail',
            $key === 'ignored_update_files' => 'general',
            default => 'general',
        };
    }

    protected function handleSignatureCache(array $validated)
    {
        $hasSignatureChanges = collect(array_keys($validated))->contains(fn ($k) => str_starts_with($k, 'signature_'));
        if ($hasSignatureChanges) {
            Cache::forget('signature_banner_type_1');
            Cache::forget('signature_banner_type_2');
            Cache::forget('signature_banner_type_3');
        }
    }

    public function testMail(Request $request)
    {
        try {
            $to = $request->get('email', auth()->user()->email);
            Mail::send('emails.test', [], function ($message) use ($to) {
                $message->to($to)->subject(config('app.name').': Test-Email');
            });

            return response()->json(['success' => true, 'message' => 'Die HTML Test-Email wurde erfolgreich versendet an '.$to]);
        } catch (\Exception $e) {
            Log::error('Mail Test failed: '.$e->getMessage());

            // Provide a more user-friendly error message
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'authentication failed')) {
                $errorMessage = 'Authentifizierung fehlgeschlagen. Bitte prüfe Benutzername und Passwort.';
            } elseif (str_contains($errorMessage, 'Connection could not be established')) {
                $errorMessage = 'Verbindung zum SMTP-Server fehlgeschlagen. Bitte prüfe Host und Port.';
            }

            return response()->json(['success' => false, 'message' => 'Fehler: '.$errorMessage], 500);
        }
    }
}
