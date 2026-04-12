<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    public function login(Request $request, string $token)
    {
        $data = Cache::get("impersonate_{$token}");

        if (!$data || $data['tenant_id'] !== tenancy()->tenant->id) {
            abort(403, 'Ungültiger oder abgelaufener Impersonation-Token.');
        }

        Cache::forget("impersonate_{$token}");

        $admin = User::where('is_admin', true)->first();

        if (!$admin) {
            abort(404, 'Kein Admin-User in dieser Instanz gefunden.');
        }

        Auth::login($admin);

        session(['impersonated_by' => $data['cadmin']]);

        Log::info("Impersonation erfolgreich: {$data['cadmin']} → Tenant " . tenancy()->tenant->id);

        return redirect()->route('dashboard');
    }

    public function exit()
    {
        session()->forget('impersonated_by');
        Auth::logout();

        $cadminUrl = config('app.url') . '/cadmin/tenants';
        return redirect()->away($cadminUrl);
    }
}
