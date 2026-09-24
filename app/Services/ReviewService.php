<?php

namespace App\Services;

use App\Enums\LeaseStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Lease;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * FR-REV-01/02: satu ulasan per kontrak, jika aktif >= N hari atau sudah selesai.
     */
    public function create(Lease $lease, User $tenant, int $rating, string $comment): Review
    {
        return DB::transaction(function () use ($lease, $tenant, $rating, $comment) {
            $lease = Lease::whereKey($lease->id)->with('room')->lockForUpdate()->firstOrFail();

            if ($lease->user_id !== $tenant->id) {
                throw new BusinessRuleException('Kontrak ini bukan milikmu.');
            }
            if ($lease->review()->exists()) {
                throw new BusinessRuleException('Kamu sudah memberi ulasan untuk kontrak ini.');
            }
            if (! $this->eligible($lease)) {
                throw new BusinessRuleException('Ulasan bisa ditulis setelah tinggal minimal '.config('kost.review_min_active_days').' hari atau setelah kontrak selesai.');
            }

            $review = new Review(['rating' => $rating, 'comment' => $comment]);
            $review->forceFill([
                'lease_id' => $lease->id,
                'user_id' => $tenant->id,
                'property_id' => $lease->room->property_id,
                'is_published' => true,
            ])->save();

            return $review;
        });
    }

    public function update(Review $review, int $rating, string $comment): Review
    {
        $review->update(['rating' => $rating, 'comment' => $comment]);

        return $review;
    }

    /**
     * FR-REV-04: sembunyikan/tampilkan (moderasi owner).
     */
    public function toggle(Review $review, User $owner): Review
    {
        $review->forceFill(['is_published' => ! $review->is_published])->save();

        activity('review')->performedOn($review)->causedBy($owner)
            ->event($review->is_published ? 'published' : 'hidden')
            ->log($review->is_published ? 'Ulasan ditampilkan' : 'Ulasan disembunyikan');

        return $review;
    }

    public function eligible(Lease $lease): bool
    {
        return $lease->status === LeaseStatus::Completed
            || ($lease->status === LeaseStatus::Active
                && $lease->start_date->lte(today()->subDays((int) config('kost.review_min_active_days'))));
    }
}
