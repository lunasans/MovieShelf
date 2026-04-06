<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        // 1. Initial load for central context
        $this->loadMailSettings();

        // 2. React to tenancy initialization
        \Illuminate\Support\Facades\Event::listen(\Stancl\Tenancy\Events\TenancyInitialized::class, function () {
            $this->loadMailSettings();
        });

        // 3. React to tenancy reverting back
        \Illuminate\Support\Facades\Event::listen(\Stancl\Tenancy\Events\TenancyEnded::class, function () {
            $this->loadMailSettings();
        });
    }

    /**
     * Load mail settings from the current database and re-initialize the Mail Manager.
     */
    protected function loadMailSettings(): void
    {
        try {
            // Check if tables exist
            if (!Schema::hasTable('settings')) {
                return;
            }

            $mailSettings = Setting::where('group', 'mail')->pluck('value', 'key');

            if ($mailSettings->isNotEmpty()) {
                $this->applyMailSettings($mailSettings);

                // IMPORTANT: In Laravel 11/12, the MailManager and individual mailers must be purged
                // to reflect the configuration changes immediately in the current request.
                \Illuminate\Support\Facades\Mail::purge(\Illuminate\Support\Facades\Config::get('mail.default'));
                app()->forgetInstance('mail.manager');
            }
        } catch (\Throwable $e) {
            // Silently fail if database is not ready
            return;
        }
    }

    /**
     * Apply the mail settings to the configuration.
     */
    protected function applyMailSettings($settings): void
    {
        if (isset($settings['mail_mailer'])) {
            Config::set('mail.default', $settings['mail_mailer']);
        }

        $this->setSmtpConfig($settings);
        $this->setFromConfig($settings);
    }

    /**
     * Set the SMTP configuration.
     */
    protected function setSmtpConfig($settings): void
    {
        $fields = [
            'mail_host' => 'mail.mailers.smtp.host',
            'mail_port' => 'mail.mailers.smtp.port',
            'mail_username' => 'mail.mailers.smtp.username',
            'mail_password' => 'mail.mailers.smtp.password',
        ];

        foreach ($fields as $key => $configKey) {
            if (isset($settings[$key])) {
                Config::set($configKey, $settings[$key]);
            }
        }

        if (isset($settings['mail_encryption'])) {
            $encryption = $settings['mail_encryption'];
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
        }
    }

    /**
     * Set the 'from' configuration.
     */
    protected function setFromConfig($settings): void
    {
        if (isset($settings['mail_from_address'])) {
            Config::set('mail.from.address', $settings['mail_from_address']);
        }

        if (isset($settings['mail_from_name'])) {
            Config::set('mail.from.name', $settings['mail_from_name']);
        }
    }
}
