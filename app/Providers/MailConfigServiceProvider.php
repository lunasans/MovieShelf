<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only attempt to load settings if the table exists
        if (Schema::hasTable('settings')) {
            $mailSettings = Setting::where('group', 'mail')->pluck('value', 'key');

            if ($mailSettings->isNotEmpty()) {
                // Set default mailer
                if (isset($mailSettings['mail_mailer'])) {
                    Config::set('mail.default', $mailSettings['mail_mailer']);
                }

                // Set SMTP details
                if (isset($mailSettings['mail_host'])) {
                    Config::set('mail.mailers.smtp.host', $mailSettings['mail_host']);
                }
                if (isset($mailSettings['mail_port'])) {
                    Config::set('mail.mailers.smtp.port', $mailSettings['mail_port']);
                }
                if (isset($mailSettings['mail_username'])) {
                    Config::set('mail.mailers.smtp.username', $mailSettings['mail_username']);
                }
                if (isset($mailSettings['mail_password'])) {
                    Config::set('mail.mailers.smtp.password', $mailSettings['mail_password']);
                }
                if (isset($mailSettings['mail_encryption'])) {
                    $encryption = $mailSettings['mail_encryption'];
                    if ($encryption === 'none') {
                        Config::set('mail.mailers.smtp.encryption', null);
                    } else {
                        Config::set('mail.mailers.smtp.encryption', $encryption);
                    }
                }

                // Set From address
                if (isset($mailSettings['mail_from_address'])) {
                    Config::set('mail.from.address', $mailSettings['mail_from_address']);
                }
                if (isset($mailSettings['mail_from_name'])) {
                    Config::set('mail.from.name', $mailSettings['mail_from_name']);
                }
            }
        }
    }
}
