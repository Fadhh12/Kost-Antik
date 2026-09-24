<?php

namespace Tests\Feature;

use App\Enums\GenderTarget;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_only_active_properties(): void
    {
        Room::factory()->for(Property::factory()->state(['name' => 'Kost Aktif Sekali']))->create();
        Room::factory()->for(Property::factory()->inactive()->state(['name' => 'Kost Tutup Renovasi']))->create();

        $this->get('/kost')->assertOk()->assertSee('Kost Aktif Sekali')->assertDontSee('Kost Tutup Renovasi');
    }

    public function test_catalog_filters_by_gender_price_and_availability(): void
    {
        Room::factory()->for(Property::factory()->forGender(GenderTarget::Female)->state(['name' => 'Kost Putri Murah']))->create(['monthly_price' => 800_000]);
        Room::factory()->for(Property::factory()->forGender(GenderTarget::Male)->state(['name' => 'Kost Putra Mahal']))->create(['monthly_price' => 2_000_000]);
        Room::factory()->occupied()->for(Property::factory()->forGender(GenderTarget::Female)->state(['name' => 'Kost Putri Penuh']))->create(['monthly_price' => 900_000]);

        $this->get('/kost?gender=female')->assertSee('Kost Putri Murah')->assertDontSee('Kost Putra Mahal');
        $this->get('/kost?max_price=1000000')->assertSee('Kost Putri Murah')->assertDontSee('Kost Putra Mahal');
        $this->get('/kost?gender=female&available=1')->assertSee('Kost Putri Murah')->assertDontSee('Kost Putri Penuh');
        $this->get('/kost?q=Mahal')->assertSee('Kost Putra Mahal')->assertDontSee('Kost Putri Murah');
    }

    public function test_invalid_filters_are_ignored(): void
    {
        $this->get('/kost?gender=alien&sort=acak&min_price=abc')->assertOk();
    }

    public function test_property_detail_shows_rooms_and_price(): void
    {
        $property = Property::factory()->create(['name' => 'Wisma Uji']);
        Room::factory()->for($property)->create(['code' => '7Z', 'monthly_price' => 1_234_000]);

        $this->get(route('kost.show', $property))
            ->assertOk()
            ->assertSee('Wisma Uji')
            ->assertSee('7Z')
            ->assertSee('Rp1.234.000');
    }

    public function test_inactive_property_detail_is_not_found(): void
    {
        $property = Property::factory()->inactive()->create();

        $this->get(route('kost.show', $property))->assertNotFound();
    }

    public function test_guest_apply_redirects_to_login_then_back_to_room(): void
    {
        $room = Room::factory()->create();
        $url = route('kost.apply', [$room->property, 'kamar' => $room->id]);

        $this->get($url)->assertRedirect(route('login'));

        $user = User::factory()->tenant()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect($url);
    }

    public function test_pending_tenant_sees_why_booking_is_blocked(): void
    {
        $room = Room::factory()->create();

        $this->actingAs(User::factory()->tenant()->pending()->create())
            ->get(route('kost.show', $room->property))
            ->assertSee('Akunmu masih menunggu verifikasi pemilik');
    }
}
