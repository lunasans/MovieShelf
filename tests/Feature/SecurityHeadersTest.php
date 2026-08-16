<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_responses_carry_security_headers()
    {
        $response = $this->get(route('impressum'));

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, 'Content-Security-Policy header fehlt');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('youtube-nocookie.com', $csp);
        // Turnstile gehoert zur Cloud und ist hier bewusst nicht freigegeben.
        $this->assertStringNotContainsString('challenges.cloudflare.com', $csp);
    }
}
