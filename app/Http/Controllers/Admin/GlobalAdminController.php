<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Setting;
use Illuminate\Http\Request;

class GlobalAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::whereNotNull('activated_at')->count(),
            'pending_tenants' => Tenant::whereNull('activated_at')->count(),
        ];

        $recent_tenants = Tenant::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_tenants'));
    }

    public function tenants()
    {
        $tenants = Tenant::with('domains')->latest()->paginate(15);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['activated_at' => now()]);
        return back()->with('success', "MovieShelf '{$tenant->id}' wurde manuell aktiviert.");
    }

    public function delete(Tenant $tenant)
    {
        $id = $tenant->id;
        $tenant->delete();
        return back()->with('success', "MovieShelf '{$id}' wurde gelöscht.");
    }

    public function settings()
    {
        $settings = [
            'saas_name' => Setting::get('saas_name', 'MovieShelf Cloud'),
            'saas_headline' => Setting::get('saas_headline', 'Dein digitales Filmregal. Überall.'),
            'support_email' => Setting::get('support_email', 'support@movieshelf.info'),
            'onboarding_mode' => Setting::get('onboarding_mode', 'manual'), // manual or auto
            'global_tmdb_key' => Setting::get('global_tmdb_key', ''),
            'default_tenant_layout' => Setting::get('default_tenant_layout', 'classic'),
            'default_tenant_language' => Setting::get('default_tenant_language', 'de'),
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => Setting::get('mail_password', ''),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'saas_name' => 'required|string|max:255',
            'saas_headline' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'onboarding_mode' => 'required|in:manual,auto',
            'global_tmdb_key' => 'nullable|string|max:255',
            'default_tenant_layout' => 'required|in:classic,streaming',
            'default_tenant_language' => 'required|in:de,en',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:20',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'saas');
        }

        return back()->with('success', 'Einstellungen wurden erfolgreich gespeichert.');
    }
}
