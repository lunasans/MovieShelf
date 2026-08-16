<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class OnboardingController extends Controller
{
    /**
     * Blendet die Onboarding-Checkliste im Regal dauerhaft aus (pro Tenant).
     */
    public function dismiss()
    {
        Setting::set('onboarding_dismissed', '1');

        return back();
    }
}
