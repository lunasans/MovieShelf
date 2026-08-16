<?php

namespace Tests\Unit;

use App\Services\ShortcodeService;
use Tests\TestCase;

class ShortcodeServiceTest extends TestCase
{
    public function test_allows_basic_formatting_tags()
    {
        $input = '<p>Alien-Horror auf 4 DVDs!</p><p><br></p><p>Ridley Scott setzte mit <em>ALIEN</em> neue Maßstäbe.</p>';

        $result = ShortcodeService::parse($input);

        $this->assertStringContainsString('<p>Alien-Horror auf 4 DVDs!</p>', $result);
        $this->assertStringContainsString('<em>ALIEN</em>', $result);
        $this->assertStringContainsString('<br>', $result);
        $this->assertStringNotContainsString('&lt;p&gt;', $result);
    }

    public function test_strips_attributes_from_allowed_tags()
    {
        $result = ShortcodeService::parse('<p onclick="evil()">Absatz</p>');

        $this->assertStringContainsString('<p>Absatz</p>', $result);
        $this->assertStringNotContainsString('onclick', $result);
    }

    public function test_keeps_disallowed_tags_escaped()
    {
        $result = ShortcodeService::parse('Hallo <script>alert(1)</script><img src=x onerror=y>');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('<img', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_plain_text_stays_untouched()
    {
        $result = ShortcodeService::parse('Ein Text mit 3 < 5 und Tom & Jerry.');

        $this->assertStringContainsString('3 &lt; 5', $result);
        $this->assertStringContainsString('Tom &amp; Jerry', $result);
    }
}
