<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

final class ReleaseSecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_applied_to_api_responses(): void
    {
        $this->getJson('/api/v1/health')->assertOk()
            ->assertHeader('X-Content-Type-Options','nosniff')
            ->assertHeader('X-Frame-Options','DENY')
            ->assertHeader('Referrer-Policy','strict-origin-when-cross-origin');
    }

    public function test_sensitive_api_responses_are_not_cacheable(): void
    {
        $response = $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
