<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use App\Services\LeaseService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAndReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_metrics_and_revenue_json(): void
    {
        $owner = User::factory()->owner()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), Room::factory()->create(['monthly_price' => 1_500_000]), today(), 2, $owner);
        app(PaymentService::class)->recordCash($lease->invoices()->first(), $owner, today());

        $this->actingAs($owner)->get('/admin/dashboard')->assertOk()->assertSee('Rp1.500.000')->assertSee('Okupansi');

        $json = $this->actingAs($owner)->getJson('/admin/dashboard/revenue-chart')->assertOk()->json();
        $this->assertCount(6, $json['labels']);
        $this->assertSame(1_500_000, end($json['values']));
    }

    public function test_manager_dashboard_is_scoped(): void
    {
        $manager = User::factory()->manager()->create();
        $owner = User::factory()->owner()->create();
        Property::factory()->create(['manager_id' => $manager->id, 'name' => 'Gedung Milik Pengelola']);
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), Room::factory()->create(['monthly_price' => 2_000_000]), today(), 1, $owner);
        app(PaymentService::class)->recordCash($lease->invoices()->first(), $owner, today());

        $this->actingAs($manager)->get('/admin/dashboard')->assertOk()->assertSee('Gedung Milik Pengelola')->assertDontSee('Rp2.000.000');
        $this->assertSame(0, array_sum($this->actingAs($manager)->getJson('/admin/dashboard/revenue-chart')->json('values')));
    }

    public function test_tenant_reviews_after_thirty_days_once(): void
    {
        Carbon::setTestNow('2026-01-01');
        $tenant = User::factory()->tenant()->create();
        $lease = app(LeaseService::class)->createManual($tenant, Room::factory()->create(), today(), 6, User::factory()->owner()->create());

        // Terlalu cepat.
        $this->actingAs($tenant)->post("/app/leases/{$lease->id}/review", ['rating' => 5, 'comment' => 'Kamarnya bersih sekali.'])->assertSessionHas('error');

        Carbon::setTestNow('2026-02-05');
        $this->actingAs($tenant)->post("/app/leases/{$lease->id}/review", ['rating' => 6, 'comment' => 'pendek'])->assertSessionHasErrors(['rating', 'comment']);
        $this->actingAs($tenant)->post("/app/leases/{$lease->id}/review", ['rating' => 5, 'comment' => 'Kamarnya bersih sekali.'])->assertSessionHas('success');
        $this->actingAs($tenant)->post("/app/leases/{$lease->id}/review", ['rating' => 4, 'comment' => 'Mencoba ulasan kedua.'])->assertSessionHas('error');
        $this->assertSame(1, Review::count());

        // Bisa diedit dalam 7 hari, setelahnya tidak.
        $review = Review::first();
        $this->actingAs($tenant)->put("/app/reviews/{$review->id}", ['rating' => 4, 'comment' => 'Revisi: kamar bersih, air lancar.'])->assertSessionHas('success');
        Carbon::setTestNow('2026-02-20');
        $this->actingAs($tenant)->put("/app/reviews/{$review->id}", ['rating' => 1, 'comment' => 'Mengubah setelah lewat batas.'])->assertForbidden();
    }

    public function test_other_tenant_cannot_review_someone_elses_lease(): void
    {
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), Room::factory()->create(), today()->subDays(40), 3, User::factory()->owner()->create());

        $this->actingAs(User::factory()->tenant()->create())->post("/app/leases/{$lease->id}/review", ['rating' => 5, 'comment' => 'Bukan kontrak saya.'])->assertForbidden();
    }

    public function test_owner_hides_review_and_rating_ignores_it(): void
    {
        $owner = User::factory()->owner()->create();
        $room = Room::factory()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), $room, today()->subDays(40), 3, $owner);
        $review = new Review(['rating' => 1, 'comment' => 'Ulasan yang tidak pantas.']);
        $review->forceFill(['lease_id' => $lease->id, 'user_id' => $lease->user_id, 'property_id' => $room->property_id])->save();

        $this->actingAs(User::factory()->manager()->create())->patch("/admin/reviews/{$review->id}/toggle")->assertForbidden();
        $this->actingAs($owner)->patch("/admin/reviews/{$review->id}/toggle")->assertSessionHas('success');

        $this->assertFalse($review->fresh()->is_published);
        $this->assertNull(Property::withCatalogStats()->find($room->property_id)->rating_avg);
    }

    public function test_payment_report_and_csv_export(): void
    {
        $owner = User::factory()->owner()->create();
        $lease = app(LeaseService::class)->createManual(User::factory()->tenant()->create(), Room::factory()->create(['monthly_price' => 750_000]), today(), 1, $owner);
        app(PaymentService::class)->recordCash($lease->invoices()->first(), $owner, today());

        $this->actingAs($owner)->get('/admin/reports/payments')->assertOk()->assertSee('Rp750.000');

        $csv = $this->actingAs($owner)->get('/admin/reports/payments?format=csv')->assertOk()->streamedContent();
        $this->assertStringContainsString('No. tagihan', $csv);
        $this->assertStringContainsString('750000', $csv);

        $this->actingAs(User::factory()->manager()->create())->get('/admin/reports/payments')->assertForbidden();
    }
}
