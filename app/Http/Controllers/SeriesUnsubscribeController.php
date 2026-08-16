<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * One-Click-Abmeldung von Serien-Benachrichtigungen über einen signierten
 * Link aus der E-Mail – funktioniert ohne Login.
 */
class SeriesUnsubscribeController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        $user->update(['notify_new_episodes' => false]);

        return view('tenant.series-unsubscribed', ['user' => $user]);
    }
}
