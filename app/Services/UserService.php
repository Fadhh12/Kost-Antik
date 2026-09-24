<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
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
}
