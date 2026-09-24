<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rizky Ananda',
            'email' => 'rizky@example.com',
            'phone' => '081234567890',
            'gender' => 'male',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_new_users_register_as_pending_tenants(): void
    {
        $response = $this->post('/register', $this->payload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::whereEmail('rizky@example.com')->first();
        $this->assertSame(UserStatus::Pending, $user->status);
        $this->assertTrue($user->isTenant());
    }

    public function test_status_cannot_be_injected_during_registration(): void
    {
        $this->post('/register', $this->payload(['status' => 'accepted']));

        $this->assertSame(UserStatus::Pending, User::whereEmail('rizky@example.com')->first()->status);
    }

    public function test_phone_and_password_rules_are_enforced(): void
    {
        $this->post('/register', $this->payload([
            'phone' => '12345',
            'password' => 'hanyahuruf',
            'password_confirmation' => 'hanyahuruf',
        ]))->assertSessionHasErrors(['phone', 'password']);

        $this->assertGuest();
    }
}
