<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Setting;
use App\Mail\TenantWelcome;

class RegisterTenantController extends Controller
{
    public function show()
    {
        return view('auth.register-tenant');
    }

    public function checkSubdomain(Request $request)
    {
        $subdomain = Str::slug($request->query('name'));
        
        if (empty($subdomain) || strlen($subdomain) < 3) {
            return response()->json(['available' => null]);
        }

        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $fullDomain = $subdomain . '.' . $centralDomain;

        $exists = \Stancl\Tenancy\Database\Models\Domain::where('domain', $fullDomain)->exists() || Tenant::where('id', $subdomain)->exists();

        return response()->json([
            'available' => !$exists,
            'slug' => $subdomain
        ]);
    }

    public function store(Request $request)
    {
        $subdomain = Str::slug($request->subdomain);
        $token = Str::random(64);
        
        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $fullDomain = $subdomain . '.' . $centralDomain;

        $request->validate([
            'subdomain' => 'required|string|alpha_dash',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $fullDomain)->exists() || Tenant::where('id', $subdomain)->exists()) {
            return back()->withErrors(['subdomain' => 'Dieser Name ist leider schon vergeben.'])->withInput();
        }

        // 0. Generate URLs before entering tenant context
        $activationUrl = route('tenant.activate', ['token' => $token]);
        $tempTenant = new Tenant(['id' => $subdomain]);
        $tenantUrl = $this->getTenantUrl($tempTenant);

        // 1. Create the Tenant (Status: Inactive)
        $tenant = new Tenant();
        $tenant->id = $subdomain;
        $tenant->email = $request->email;
        $tenant->activation_token = $token;
        $tenant->activated_at = null;
        $tenant->save();

        $tenant->domains()->create([
            'domain' => $fullDomain,
        ]);

        // 2. Apply Default Settings from Central Admin
        $defaultLayout = Setting::get('default_tenant_layout', 'classic');
        $defaultLanguage = Setting::get('default_tenant_language', 'de');

        $tenant->run(function () use ($defaultLayout, $defaultLanguage) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'site_layout'],
                ['value' => $defaultLayout, 'group' => 'pwa', 'updated_at' => now()]
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'app_language'],
                ['value' => $defaultLanguage, 'group' => 'pwa', 'updated_at' => now()]
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'site_title'],
                ['value' => 'Mein MovieShelf', 'group' => 'pwa', 'updated_at' => now()]
            );
        });

        // 3. Create the User inside the Tenant context
        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        $tenant->run(function () use ($userData) {
            User::create($userData);
        });

        // 4. Send Activation Email
        Mail::to($request->email)->send(new TenantWelcome($tenant, new User($userData), $activationUrl, $tenantUrl));

        return redirect()->route('landing')->with('success', 'Dein MovieShelf wurde erfolgreich reserviert! Bitte schaue in dein E-Mail Postfach (' . $request->email . '), um dein Filmregal freizuschalten.');
    }

    public function activate($token)
    {
        $tenant = Tenant::where('activation_token', $token)->firstOrFail();

        if ($tenant->activated_at) {
            return redirect()->away($this->getTenantUrl($tenant))->with('info', 'Dein MovieShelf ist bereits aktiviert.');
        }

        $tenant->update([
            'activated_at' => now(),
        ]);

        return redirect()->away($this->getTenantUrl($tenant))->with('success', 'Willkommen! Dein MovieShelf wurde erfolgreich aktiviert.');
    }

    protected function getTenantUrl($tenant)
    {
        $domainRecord = $tenant->domains()->first();
        $centralDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $hostname = $domainRecord ? $domainRecord->domain : $tenant->id . '.' . $centralDomain;
        
        $port = request()->getPort();
        return 'https://' . $hostname . ($port && $port != 80 && $port != 443 ? ':' . $port : '') . '/login';
    }
}
