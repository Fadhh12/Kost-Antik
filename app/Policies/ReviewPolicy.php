<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /** FR-REV-03: penulis, maksimal N hari setelah dibuat. */
    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id && $review->isEditable();
    }

    /** FR-REV-04: moderasi hanya owner. */
    public function moderate(User $user): bool
    {
        return false;
    }
}
