<?php

namespace App\Http\Controllers\Cadmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Mail\TenantActivated;
use App\Mail\TenantWelcome;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        return view('cadmin.saas_dashboard', compact('stats', 'recent_tenants'));
    }

    public function tenants()
    {
        $tenants = Tenant::with('domains')->latest()->paginate(15);
        $onboardingMode = Setting::get('onboarding_mode', 'email');

        $tenantStats = [];
        foreach ($tenants as $tenant) {
            try {
                $stats = $tenant->run(function () {
                    return [
                        'movies'        => \App\Models\Movie::where('is_deleted', false)->count(),
                        'last_activity' => \Illuminate\Support\Facades\DB::table('sessions')
                                            ->whereNotNull('user_id')
                                            ->max('last_activity'),
                    ];
                });
            } catch (\Exception $e) {
                $stats = ['movies' => '–', 'last_activity' => null];
            }

            $storagePath = storage_path("tenant{$tenant->id}/app/public");
            $stats['storage_kb'] = is_dir($storagePath) ? $this->dirSizeKb($storagePath) : 0;
            $tenantStats[$tenant->id] = $stats;
        }

        return view('cadmin.tenants.index', compact('tenants', 'onboardingMode', 'tenantStats'));
    }

    protected function dirSizeKb(string $path): int
    {
        $bytes = 0;
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $bytes += $file->getSize();
            }
        } catch (\Exception $e) {}
        return (int) round($bytes / 1024);
    }

    public function impersonate(Tenant $tenant)
    {
        $adminUser = $tenant->run(fn() => \App\Models\User::where('is_admin', true)->first());

        if (!$adminUser) {
            return back()->with('error', "Kein Admin-User in Tenant '{$tenant->id}' gefunden.");
        }

        $token = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\Cache::put(
            "impersonate_{$token}",
            ['tenant_id' => $tenant->id, 'cadmin' => auth()->user()->email],
            now()->addMinutes(5)
        );

        Log::info("Cadmin impersonation: " . auth()->user()->email . " → tenant {$tenant->id}");

        $domain = $tenant->domains()->first()?->domain;
        if (!$domain) {
            $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $domain = $tenant->id . '.' . $centralDomain;
        }

        return redirect()->away("https://{$domain}/impersonate/{$token}");
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['activated_at' => now()]);

        // Send "your shelf is now active" email to tenant owner
        if ($tenant->email) {
            try {
                $tenantUrl = $this->getTenantUrl($tenant);
                $user = $tenant->run(fn () => User::where('is_admin', true)->first());
                if ($user) {
                    Mail::to($tenant->email)->send(new TenantActivated($tenant, $user, $tenantUrl));
                }
            } catch (\Exception $e) {
                Log::error("Activation mail failed for tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        return back()->with('success', "MovieShelf '{$tenant->id}' wurde manuell aktiviert.");
    }

    protected function getTenantUrl(Tenant $tenant): string
    {
        $domainRecord = $tenant->domains()->first();
        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $hostname = $domainRecord ? $domainRecord->domain : $tenant->id . '.' . $centralDomain;
        return 'https://' . $hostname . '/login';
    }

    public function resendActivationMail(Tenant $tenant)
    {
        if (!$tenant->email) {
            return back()->with('error', "Kein E-Mail für Tenant '{$tenant->id}' hinterlegt.");
        }

        try {
            $tenantUrl = $this->getTenantUrl($tenant);
            $user = $tenant->run(fn () => User::where('is_admin', true)->first());

            if (!$user) {
                return back()->with('error', "Kein Admin-User für Tenant '{$tenant->id}' gefunden.");
            }

            if ($tenant->activated_at) {
                Mail::to($tenant->email)->send(new TenantActivated($tenant, $user, $tenantUrl));
            } else {
                $activationUrl = route('tenant.activate', ['token' => $tenant->activation_token]);
                Mail::to($tenant->email)->send(new TenantWelcome($tenant, $user, $activationUrl, $tenantUrl));
            }

            Log::info("Cadmin resent activation mail for tenant {$tenant->id}");
        } catch (\Exception $e) {
            Log::error("Resend activation mail failed for tenant {$tenant->id}: " . $e->getMessage());
            return back()->with('error', "E-Mail konnte nicht gesendet werden: " . $e->getMessage());
        }

        return back()->with('success', "Aktivierungs-E-Mail wurde erneut an {$tenant->email} gesendet.");
    }

    public function delete(Tenant $tenant)
    {
        $id = $tenant->id;

        $storagePath = storage_path("tenant{$id}");
        if (File::exists($storagePath)) {
            File::deleteDirectory($storagePath);
            Log::info("Admin deleted storage for tenant: {$id}");
        }

        $tenant->domains()->delete();
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
            'forbidden_subdomains' => Setting::get('forbidden_subdomains', 'admin,api,www,support,mail,test,dev,internal'),
            'saas_impressum_active' => Setting::get('saas_impressum_active', '0'),
            'saas_impressum_content' => Setting::get('saas_impressum_content', '<h1>Impressum</h1><p>...</p>'),
            'announcement_active' => Setting::get('announcement_active', '0'),
            'announcement_text'   => Setting::get('announcement_text', ''),
            'announcement_type'   => Setting::get('announcement_type', 'info'),
        ];

        return view('cadmin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'saas_name' => 'required|string|max:255',
            'saas_headline' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'onboarding_mode' => 'required|in:manual,auto,email',
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
            'forbidden_subdomains' => 'nullable|string',
            'saas_impressum_active' => 'required|in:0,1',
            'saas_impressum_content' => 'nullable|string',
            'announcement_active' => 'required|in:0,1',
            'announcement_text'   => 'nullable|string|max:500',
            'announcement_type'   => 'required|in:info,warning,critical',
        ]);

        // Sanitize forbidden_subdomains: ensure each entry is lowercase alphanumeric+hyphens, min 2 chars
        if (isset($data['forbidden_subdomains'])) {
            $data['forbidden_subdomains'] = collect(explode(',', $data['forbidden_subdomains']))
                ->map(fn($w) => preg_replace('/[^a-z0-9-]/', '', strtolower(trim($w))))
                ->filter(fn($w) => strlen($w) >= 2)
                ->unique()
                ->sort()
                ->implode(',');
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'saas');
        }

        // Write announcement to shared file so all tenant contexts can read it
        file_put_contents(storage_path('app/announcement.json'), json_encode([
            'active' => ($data['announcement_active'] ?? '0') === '1',
            'text'   => $data['announcement_text'] ?? '',
            'type'   => $data['announcement_type'] ?? 'info',
        ]));

        return back()->with('success', 'Einstellungen wurden erfolgreich gespeichert.');
    }

    public function testMail(Request $request)
    {
        try {
            $to = $request->get('email', auth()->user()->email ?? config('mail.from.address'));

            config([
                'mail.mailers.smtp.host'       => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port'       => Setting::get('mail_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.username'   => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password'   => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
                'mail.from.address'            => Setting::get('mail_from_address', config('mail.from.address')),
                'mail.from.name'               => Setting::get('mail_from_name', config('mail.from.name')),
            ]);
            \Illuminate\Support\Facades\Mail::purge('smtp');

            \Illuminate\Support\Facades\Mail::send('emails.test', [], function ($message) use ($to) {
                $message->to($to)->subject(config('app.name').': SMTP Verbindungstest (Central)');
            });

            return response()->json(['success' => true, 'message' => 'Die HTML Test-Email wurde erfolgreich versendet an '.$to]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Central Mail Test failed: '.$e->getMessage());

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'authentication failed')) {
                $errorMessage = 'Authentifizierung fehlgeschlagen. Bitte prüfe Benutzername und Passwort.';
            } elseif (str_contains($errorMessage, 'Connection could not be established')) {
                $errorMessage = 'Verbindung zum SMTP-Server fehlgeschlagen. Bitte prüfe Host und Port.';
            }

            return response()->json(['success' => false, 'message' => 'Fehler: '.$errorMessage], 500);
        }
    }
    public function logs()
    {
        return view('cadmin.logs');
    }
}
