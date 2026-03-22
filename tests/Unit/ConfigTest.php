<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_database_config_has_v1_connections()
    {
        $connections = config('database.connections');

        $this->assertArrayHasKey('sqlite_v1', $connections);
        $this->assertArrayHasKey('mysql_v1', $connections);
        
        $this->assertEquals('sqlite', $connections['sqlite_v1']['driver']);
        $this->assertEquals('mysql', $connections['mysql_v1']['driver']);
    }

    public function test_logging_config_uses_custom_path()
    {
        $channels = config('logging.channels');

        $this->assertArrayHasKey('single', $channels);
        $this->assertStringContainsString('laravel.log', $channels['single']['path']);
    }

    public function test_database_default_host_is_defined()
    {
        $this->assertTrue(defined('DEFAULT_HOST'));
        $this->assertEquals('127.0.0.1', DEFAULT_HOST);
    }
}
