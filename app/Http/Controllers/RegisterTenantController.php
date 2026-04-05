<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $centralDomain = request()->getHost();
        if ($centralDomain === '127.0.0.1') {
            $centralDomain = 'localhost'; // test.localhost resolves automatically on Windows
        }
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
        
        $centralDomain = request()->getHost();
        if ($centralDomain === '127.0.0.1') {
            $centralDomain = 'localhost';
        }
        $fullDomain = $subdomain . '.' . $centralDomain;

        $request->validate([
            'subdomain' => 'required|string|alpha_dash',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $subdomain)->exists() || Tenant::where('id', $subdomain)->exists()) {
            return back()->withErrors(['subdomain' => 'Dieser Name ist leider schon vergeben.'])->withInput();
        }

        // 1. Create the Tenant (Status: Inactive)
        $tenant = Tenant::create([
            'id' => $subdomain,
            'tenancy_db_driver' => 'sqlite',
            'activation_token' => $token,
            'activated_at' => null,
            'email' => $request->email,
        ]);

        $tenant->domains()->create([
            'domain' => $fullDomain,
        ]);

        // 2.5 Apply Default Settings from Central Admin
        $defaultLayout = \App\Models\Setting::get('default_tenant_layout', 'classic');
        $defaultLanguage = \App\Models\Setting::get('default_tenant_language', 'de');

        $tenant->run(function () use ($defaultLayout, $defaultLanguage) {
            \DB::table('settings')->updateOrInsert(
                ['key' => 'site_layout'],
                ['value' => $defaultLayout, 'group' => 'pwa', 'updated_at' => now()]
            );
            \DB::table('settings')->updateOrInsert(
                ['key' => 'app_language'],
                ['value' => $defaultLanguage, 'group' => 'pwa', 'updated_at' => now()]
            );
            \DB::table('settings')->updateOrInsert(
                ['key' => 'site_title'],
                ['value' => 'Mein MovieShelf', 'group' => 'pwa', 'updated_at' => now()]
            );
        });

        // 3. Create the User inside the Tenant context
        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ];

        $tenant->run(function () use ($userData) {
            \App\Models\User::create($userData);
        });

        // 4. Send Activation Email
        $activationUrl = route('tenant.activate', ['token' => $token]);
        $tenantUrl = $this->getTenantUrl($tenant);
        
        \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\TenantWelcome($tenant, new \App\Models\User($userData), $activationUrl, $tenantUrl));

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
        $hostname = $domainRecord ? $domainRecord->domain : $tenant->id . '.localhost';
        
        $port = request()->getPort();
        return 'http://' . $hostname . ($port && $port != 80 && $port != 443 ? ':' . $port : '') . '/login';
    }
}
