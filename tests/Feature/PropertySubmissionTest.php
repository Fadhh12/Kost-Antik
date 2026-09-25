<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Models\Property;
use App\Models\PropertySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertySubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kost Melati Jababeka',
            'address' => 'Jl. Melati No. 3',
            'city' => 'Cikarang',
            'gender_target' => 'female',
            'contact_name' => 'Bu Sari',
            'contact_phone' => '081234567890',
            'description' => 'Dekat gerbang Jababeka, 5 menit ke kawasan industri.',
        ], $overrides);
    }

    public function test_guest_can_submit_a_kost_registration(): void
    {
        Storage::fake('public');

        $this->get('/daftar-kost')->assertOk();

        $this->post('/daftar-kost', $this->payload([
            'photos' => [UploadedFile::fake()->image('depan.jpg')],
        ]))->assertSessionHasNoErrors()->assertRedirect(route('kost.submissions.create'));

        $submission = PropertySubmission::firstWhere('name', 'Kost Melati Jababeka');
        $this->assertNotNull($submission);
        $this->assertTrue($submission->status === SubmissionStatus::Pending);
        $this->assertCount(1, $submission->photos);
        Storage::disk('public')->assertExists($submission->photos[0]);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post('/daftar-kost', [])
            ->assertSessionHasErrors(['name', 'address', 'city', 'gender_target', 'contact_name', 'contact_phone']);
    }

    public function test_owner_sees_pending_submissions_in_admin(): void
    {
        $submission = PropertySubmission::factory()->create(['name' => 'Kost Anggrek']);

        $this->actingAs(User::factory()->owner()->create())
            ->get('/admin/property-submissions')
            ->assertOk()
            ->assertSee('Kost Anggrek');
    }

    public function test_manager_cannot_access_submission_review(): void
    {
        $this->actingAs(User::factory()->manager()->create())
            ->get('/admin/property-submissions')
            ->assertForbidden();
    }

    public function test_owner_approves_submission_and_creates_active_property(): void
    {
        Storage::fake('public');
        $owner = User::factory()->owner()->create();
        $photo = UploadedFile::fake()->image('depan.jpg')->store('submissions/temp', 'public');
        $submission = PropertySubmission::factory()->create(['photos' => [$photo]]);

        $this->actingAs($owner)
            ->post("/admin/property-submissions/{$submission->id}/approve")
            ->assertRedirect(route('admin.property-submissions.index'));

        $submission->refresh();
        $this->assertTrue($submission->status === SubmissionStatus::Approved);
        $this->assertSame($owner->id, $submission->reviewed_by);
        $this->assertNotNull($submission->created_property_id);

        $property = Property::find($submission->created_property_id);
        $this->assertSame($submission->name, $property->name);
        $this->assertTrue($property->status->value === 'active');
        $this->assertSame($owner->id, $property->manager_id);
        $this->assertCount(1, $property->images);
    }

    public function test_owner_rejects_submission_with_reason(): void
    {
        $owner = User::factory()->owner()->create();
        $submission = PropertySubmission::factory()->create();

        $this->actingAs($owner)
            ->post("/admin/property-submissions/{$submission->id}/reject", ['reason' => 'Alamat tidak valid'])
            ->assertRedirect(route('admin.property-submissions.index'));

        $submission->refresh();
        $this->assertTrue($submission->status === SubmissionStatus::Rejected);
        $this->assertSame('Alamat tidak valid', $submission->rejection_reason);
        $this->assertNull($submission->created_property_id);
    }

    public function test_reviewed_submission_cannot_be_reviewed_again(): void
    {
        $owner = User::factory()->owner()->create();
        $submission = PropertySubmission::factory()->create(['status' => SubmissionStatus::Approved]);

        $this->actingAs($owner)
            ->post("/admin/property-submissions/{$submission->id}/approve")
            ->assertRedirect();

        $this->assertSame('Pengajuan ini sudah ditinjau sebelumnya.', session('error'));
    }
}
