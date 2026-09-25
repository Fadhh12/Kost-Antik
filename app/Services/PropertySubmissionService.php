<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\Role;
use App\Enums\SubmissionStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Property;
use App\Models\PropertySubmission;
use App\Models\User;
use App\Notifications\NewPropertySubmissionNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class PropertySubmissionService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $photos
     */
    public function submit(array $data, array $photos): PropertySubmission
    {
        $submission = DB::transaction(function () use ($data, $photos) {
            $submission = PropertySubmission::create($data + ['status' => SubmissionStatus::Pending]);

            if ($photos) {
                $submission->update(['photos' => $this->storePhotos($submission, $photos)]);
            }

            return $submission;
        });

        Notification::send(User::role(Role::Owner->value)->get(), new NewPropertySubmissionNotification($submission));

        return $submission;
    }

    /**
     * Pengajuan disetujui: buat Property aktif, salin foto, tugaskan ke admin yang menyetujui.
     */
    public function approve(PropertySubmission $submission, User $actor): Property
    {
        $this->ensurePending($submission);

        return DB::transaction(function () use ($submission, $actor) {
            $property = Property::create([
                'manager_id' => $actor->id,
                'name' => $submission->name,
                'address' => $submission->address,
                'city' => $submission->city,
                'latitude' => $submission->latitude,
                'longitude' => $submission->longitude,
                'gender_target' => $submission->gender_target,
                'description' => $submission->description,
                'status' => PropertyStatus::Active,
            ]);

            foreach (($submission->photos ?? []) as $i => $oldPath) {
                $newPath = "properties/{$property->id}/".basename($oldPath);

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->copy($oldPath, $newPath);
                }

                $property->images()->create([
                    'path' => $newPath,
                    'is_cover' => $i === 0,
                    'sort_order' => $i + 1,
                ]);
            }

            $submission->update([
                'status' => SubmissionStatus::Approved,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'created_property_id' => $property->id,
            ]);

            activity('property-submission')->performedOn($submission)->causedBy($actor)->event('approved')->log('Pengajuan kost disetujui');

            return $property;
        });
    }

    public function reject(PropertySubmission $submission, User $actor, string $reason): void
    {
        $this->ensurePending($submission);

        $submission->update([
            'status' => SubmissionStatus::Rejected,
            'reviewed_by' => $actor->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        activity('property-submission')->performedOn($submission)->causedBy($actor)->event('rejected')->log('Pengajuan kost ditolak');
    }

    private function ensurePending(PropertySubmission $submission): void
    {
        if ($submission->status !== SubmissionStatus::Pending) {
            throw new BusinessRuleException('Pengajuan ini sudah ditinjau sebelumnya.');
        }
    }

    /**
     * @param  list<UploadedFile>  $photos
     * @return list<string>
     */
    private function storePhotos(PropertySubmission $submission, array $photos): array
    {
        return collect($photos)
            ->map(fn (UploadedFile $file) => $file->store("submissions/{$submission->id}", 'public'))
            ->all();
    }
}
