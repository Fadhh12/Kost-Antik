<?php

namespace App\View\Composers;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\BookingRequest;
use App\Models\Payment;
use App\Models\PropertySubmission;
use App\Models\User;
use Illuminate\View\View;

/**
 * SRS 2.4 (P1): badge di sidebar admin untuk booking, pembayaran, dan akun yang menunggu.
 */
class AdminCountsComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user?->isStaff()) {
            return;
        }

        $view->with('adminCounts', once(fn () => [
            'bookings' => BookingRequest::forManager($user)->where('status', BookingStatus::Pending)->count(),
            'payments' => Payment::forManager($user)->where('status', PaymentStatus::Pending)->count(),
            'users' => $user->isOwner()
                ? User::role(Role::Tenant->value)->where('status', UserStatus::Pending)->count()
                : 0,
            'propertySubmissions' => $user->isOwner() ? PropertySubmission::query()->pending()->count() : 0,
        ]));
    }
}
