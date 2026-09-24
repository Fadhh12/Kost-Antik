<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed_for_each_role(): void
    {
        foreach (['tenant', 'manager', 'owner'] as $role) {
            $this->actingAs(User::factory()->{$role}()->create())->get('/profile')->assertOk()->assertSee('Profil saya');
        }
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->tenant()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'phone' => '0812 3456 7890',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Dewi Lestari', $user->name);
        $this->assertSame('081234567890', $user->phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->tenant()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'Nama Baru', 'email' => $user->email, 'phone' => $user->phone])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_photo_can_be_uploaded(): void
    {
        Storage::fake('public');
        $user = User::factory()->tenant()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'photo' => UploadedFile::fake()->image('saya.jpg'),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists($user->refresh()->photo_path);
    }

    public function test_verified_user_cannot_change_gender(): void
    {
        $user = User::factory()->tenant()->male()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => 'female',
        ])->assertSessionHasErrors('gender');

        $this->assertSame(Gender::Male, $user->refresh()->gender);
    }
}
