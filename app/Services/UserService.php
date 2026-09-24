<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Exceptions\BusinessRuleException;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * FR-AUTH-01: registrasi publik selalu tenant dengan status pending.
     *
     * @param  array{name:string,email:string,phone:string,gender:string,instance_id?:int|null,password:string}  $data
     */
    public function registerTenant(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = new User($data);
            $user->status = UserStatus::Pending;
            $user->save();
            $user->assignRole(Role::Tenant->value);

            return $user;
        });
    }

    /**
     * F-07: ubah data diri & foto profil (disk public).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($photo || $removePhoto) {
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $user->photo_path = $photo?->store('avatars', 'public');
        }

        $user->save();

        return $user;
    }

    /**
     * FR-USER-01: setujui penyewa pending.
     */
    public function approve(User $target, User $actor): User
    {
        $this->assertStatus($target, [UserStatus::Pending, UserStatus::Rejected], 'Hanya akun menunggu atau ditolak yang bisa disetujui.');

        return $this->changeStatus($target, UserStatus::Accepted, null, $actor, 'approved', 'Akun disetujui');
    }

    /**
     * FR-USER-01: tolak penyewa pending (wajib alasan).
     */
    public function reject(User $target, string $reason, User $actor): User
    {
        $this->assertStatus($target, [UserStatus::Pending], 'Hanya akun yang menunggu verifikasi yang bisa ditolak.');

        return $this->changeStatus($target, UserStatus::Rejected, $reason, $actor, 'rejected', 'Akun ditolak');
    }

    /**
     * FR-USER-03: nonaktifkan (soft-disable). Penyewa dengan kontrak aktif tidak bisa dinonaktifkan.
     */
    public function deactivate(User $target, string $reason, User $actor): User
    {
        if ($target->is($actor)) {
            throw new BusinessRuleException('Kamu tidak bisa menonaktifkan akunmu sendiri.');
        }
        if ($target->isOwner()) {
            throw new BusinessRuleException('Akun pemilik tidak bisa dinonaktifkan.');
        }
        if ($target->hasActiveLease()) {
            throw new BusinessRuleException('Penyewa masih punya kontrak aktif. Akhiri kontraknya lebih dulu.');
        }

        return DB::transaction(function () use ($target, $reason, $actor) {
            // Booking pending ikut dibatalkan agar tidak diproses.
            $target->bookings()->where('status', BookingStatus::Pending)->update([
                'status' => BookingStatus::Cancelled,
                'reject_reason' => 'Akun penyewa dinonaktifkan.',
                'updated_at' => now(),
            ]);

            if ($target->isManager()) {
                $target->managedProperties()->update(['manager_id' => null]);
            }

            return $this->changeStatus($target, UserStatus::Inactive, $reason, $actor, 'deactivated', 'Akun dinonaktifkan');
        });
    }

    public function reactivate(User $target, User $actor): User
    {
        $this->assertStatus($target, [UserStatus::Inactive], 'Hanya akun nonaktif yang bisa diaktifkan kembali.');

        return $this->changeStatus($target, UserStatus::Accepted, null, $actor, 'reactivated', 'Akun diaktifkan kembali');
    }

    /**
     * FR-USER-02: owner membuat akun pengelola (langsung accepted) dan menugaskan gedung.
     *
     * @param  array{name:string,email:string,phone:string,gender:string,password:string}  $data
     * @param  list<int>  $propertyIds
     */
    public function createManager(array $data, array $propertyIds, User $actor): User
    {
        return DB::transaction(function () use ($data, $propertyIds, $actor) {
            $user = new User($data);
            $user->status = UserStatus::Accepted;
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole(Role::Manager->value);

            if ($propertyIds) {
                Property::whereIn('id', $propertyIds)->update(['manager_id' => $user->id]);
            }

            activity('user')->performedOn($user)->causedBy($actor)->event('manager_created')
                ->withProperties(['properties' => $propertyIds])
                ->log('Akun pengelola dibuat');

            return $user;
        });
    }

    /**
     * @param  list<UserStatus>  $allowed
     */
    private function assertStatus(User $target, array $allowed, string $message): void
    {
        if (! in_array($target->status, $allowed, true)) {
            throw new BusinessRuleException($message);
        }
    }

    private function changeStatus(User $target, UserStatus $status, ?string $reason, User $actor, string $event, string $log): User
    {
        return DB::transaction(function () use ($target, $status, $reason, $actor, $event, $log) {
            $target->forceFill(['status' => $status, 'status_reason' => $reason])->save();

            activity('user')->performedOn($target)->causedBy($actor)->event($event)
                ->withProperties(array_filter(['reason' => $reason]))
                ->log($log);

            return $target;
        });
    }
}
