<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_account_cannot_login_and_sees_reason(): void
    {
        $user = User::factory()->tenant()->rejected('Foto KTP buram.')->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Pendaftaran akunmu ditolak. Foto KTP buram.']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->tenant()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_pending_tenant_can_login_and_sees_banner(): void
    {
        $user = User::factory()->tenant()->pending()->create();

        $this->actingAs($user)->get('/app/dashboard')
            ->assertOk()
            ->assertSee('Akun menunggu verifikasi');
    }

    public function test_session_ends_when_account_is_deactivated(): void
    {
        $user = User::factory()->tenant()->create();
        $this->actingAs($user);

        $user->forceFill(['status' => UserStatus::Inactive])->save();

        $this->get('/app/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $this->actingAs(User::factory()->owner()->create())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs(User::factory()->manager()->create())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs(User::factory()->tenant()->create())->get('/dashboard')->assertRedirect(route('app.dashboard'));
    }

    public function test_areas_are_restricted_by_role(): void
    {
        $this->actingAs(User::factory()->tenant()->create())->get('/admin/dashboard')->assertForbidden();
        $this->actingAs(User::factory()->manager()->create())->get('/app/dashboard')->assertForbidden();
        $this->actingAs(User::factory()->manager()->create())->get('/admin/dashboard')->assertOk();
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        $user = User::factory()->tenant()->create();

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
