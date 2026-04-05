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
        try {
            // Check if it's a central connection and if the database file exists (for SQLite)
            $connection = Config::get('database.default');
            $dbPath = Config::get("database.connections.{$connection}.database");

            if (!$dbPath) {
                return;
            }

            if (Config::get("database.connections.{$connection}.driver") === 'sqlite' && 
                $dbPath !== ':memory:' && 
                !file_exists($dbPath)) {
                return;
            }

            // Wrap Schema check in try-catch because SQLiteConnector can throw 
            // if the path is invalid before it even attempts the query
            try {
                if (!Schema::hasTable('settings')) {
                    return;
                }
            } catch (\Throwable $e) {
                return;
            }

            $mailSettings = Setting::where('group', 'mail')->pluck('value', 'key');

            if ($mailSettings->isNotEmpty()) {
                $this->applyMailSettings($mailSettings);
            }
        } catch (\Throwable $e) {
            // Silently fail during boot if database is not ready
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
