<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_admin_pages_include_baseline_security_headers(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self)');
    }
}
