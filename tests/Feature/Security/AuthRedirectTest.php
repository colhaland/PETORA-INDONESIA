<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_route(): void
    {
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/pos')->assertRedirect('/login');
        $this->get('/settings')->assertRedirect('/login');
    }
}
