<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
}
