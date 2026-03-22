<?php

namespace Tests\Feature\Providers;

use App\Models\Setting;
use App\Providers\MailConfigServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MailConfigServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Backup original config to restore after each test if needed
        $this->originalConfig = Config::get('mail');
    }

    public function test_it_applies_mail_settings_from_database()
    {
        // 1. Seed mail settings
        Setting::create(['group' => 'mail', 'key' => 'mail_mailer', 'value' => 'smtp']);
        Setting::create(['group' => 'mail', 'key' => 'mail_host', 'value' => 'smtp.testserver.com']);
        Setting::create(['group' => 'mail', 'key' => 'mail_port', 'value' => '587']);
        Setting::create(['group' => 'mail', 'key' => 'mail_username', 'value' => 'testuser']);
        Setting::create(['group' => 'mail', 'key' => 'mail_password', 'value' => 'secret123']);
        Setting::create(['group' => 'mail', 'key' => 'mail_encryption', 'value' => 'tls']);
        Setting::create(['group' => 'mail', 'key' => 'mail_from_address', 'value' => 'no-reply@test.com']);
        Setting::create(['group' => 'mail', 'key' => 'mail_from_name', 'value' => 'Test App']);

        // 2. Instantiate and boot the provider manually
        $provider = new MailConfigServiceProvider($this->app);
        $provider->boot();

        // 3. Assert config overrides
        $this->assertEquals('smtp', Config::get('mail.default'));
        $this->assertEquals('smtp.testserver.com', Config::get('mail.mailers.smtp.host'));
        $this->assertEquals('587', Config::get('mail.mailers.smtp.port'));
        $this->assertEquals('testuser', Config::get('mail.mailers.smtp.username'));
        $this->assertEquals('secret123', Config::get('mail.mailers.smtp.password'));
        $this->assertEquals('tls', Config::get('mail.mailers.smtp.encryption'));
        $this->assertEquals('no-reply@test.com', Config::get('mail.from.address'));
        $this->assertEquals('Test App', Config::get('mail.from.name'));
    }

    public function test_it_handles_none_encryption_as_null()
    {
        Setting::create(['group' => 'mail', 'key' => 'mail_encryption', 'value' => 'none']);

        $provider = new MailConfigServiceProvider($this->app);
        $provider->boot();

        $this->assertNull(Config::get('mail.mailers.smtp.encryption'));
    }

    public function test_it_does_nothing_if_settings_table_is_missing()
    {
        // Simulate missing table (e.g. using a mock or simply in a state where Schema::hasTable returns false)
        // Since we are using RefreshDatabase, the table exists. 
        // To test the early-exit, we can mock Schema facade.
        
        Schema::shouldReceive('hasTable')
            ->with('settings')
            ->once()
            ->andReturn(false);

        // This should not throw any exception even if we have logic that depends on the table
        $provider = new MailConfigServiceProvider($this->app);
        $provider->boot();
        
        // Ensure no config was changed from default (or original)
        $this->assertEquals($this->originalConfig['default'], Config::get('mail.default'));
    }

    public function test_it_handles_partial_settings()
    {
        Config::set('mail.mailers.smtp.host', 'original.host');
        
        Setting::create(['group' => 'mail', 'key' => 'mail_host', 'value' => 'new.host']);
        // No other settings

        $provider = new MailConfigServiceProvider($this->app);
        $provider->boot();

        $this->assertEquals('new.host', Config::get('mail.mailers.smtp.host'));
        $this->assertEquals($this->originalConfig['from']['address'], Config::get('mail.from.address'));
    }
}
