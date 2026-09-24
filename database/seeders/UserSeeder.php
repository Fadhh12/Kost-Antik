<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Instance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $instances = Instance::pluck('id')->all();

        // Akun demo (lihat README).
        $this->make('Nabil Fadhlur Rahman', 'owner@kostantik.test', '081200000001', Gender::Male, Role::Owner, UserStatus::Accepted, $password);
        $this->make('Sri Wahyuni', 'manager@kostantik.test', '081200000002', Gender::Female, Role::Manager, UserStatus::Accepted, $password);
        $this->make('Hendra Gunawan', 'manager2@kostantik.test', '081200000003', Gender::Male, Role::Manager, UserStatus::Accepted, $password);
        $this->make('Rizky Ananda', 'tenant@kostantik.test', '081200000004', Gender::Male, Role::Tenant, UserStatus::Accepted, $password, $instances[0] ?? null);
        $this->make('Putri Maharani', 'tenant2@kostantik.test', '081200000005', Gender::Female, Role::Tenant, UserStatus::Accepted, $password, $instances[1] ?? null);

        // Penyewa lain dengan status campuran.
        $tenants = [
            ['Bagas Pratama', Gender::Male], ['Dimas Saputra', Gender::Male], ['Fajar Nugroho', Gender::Male],
            ['Galih Permana', Gender::Male], ['Ilham Maulana', Gender::Male], ['Joko Susilo', Gender::Male],
            ['Kevin Wijaya', Gender::Male], ['Lutfi Hakim', Gender::Male], ['Muhammad Iqbal', Gender::Male],
            ['Naufal Ramadhan', Gender::Male], ['Oki Setiawan', Gender::Male], ['Raka Aditya', Gender::Male],
            ['Ayu Lestari', Gender::Female], ['Citra Kirana', Gender::Female], ['Dewi Anggraini', Gender::Female],
            ['Eka Safitri', Gender::Female], ['Fitri Handayani', Gender::Female], ['Gita Puspita', Gender::Female],
            ['Hana Rahmawati', Gender::Female], ['Indah Permatasari', Gender::Female], ['Kartika Sari', Gender::Female],
            ['Laras Wulandari', Gender::Female], ['Maya Septiani', Gender::Female], ['Nadia Paramita', Gender::Female],
            ['Salsabila Azzahra', Gender::Female],
        ];

        // Sebagian kecil penyewa belum/tidak aktif untuk demo alur verifikasi akun.
        $special = [
            4 => UserStatus::Pending,
            11 => UserStatus::Pending,
            17 => UserStatus::Pending,
            21 => UserStatus::Rejected,
            24 => UserStatus::Inactive,
        ];

        foreach ($tenants as $i => [$name, $gender]) {
            $status = $special[$i] ?? UserStatus::Accepted;

            $email = strtolower(str_replace(' ', '.', $name)).'@mail.test';
            $user = $this->make($name, $email, '0813'.str_pad((string) (10000000 + $i * 137), 8, '0', STR_PAD_LEFT), $gender, Role::Tenant, $status, $password, $instances[$i % max(1, count($instances))] ?? null);

            if ($status === UserStatus::Rejected) {
                $user->forceFill(['status_reason' => 'Foto KTP tidak terbaca. Silakan daftar ulang dengan data yang jelas.'])->save();
            }
            if ($status === UserStatus::Inactive) {
                $user->forceFill(['status_reason' => 'Sudah pindah domisili.'])->save();
            }
        }
    }

    private function make(string $name, string $email, string $phone, Gender $gender, Role $role, UserStatus $status, string $password, ?int $instanceId = null): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->fill([
            'name' => $name,
            'phone' => $phone,
            'gender' => $gender,
            'instance_id' => $role === Role::Tenant ? $instanceId : null,
        ]);
        $user->password = $password;
        $user->status = $status;
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles([$role->value]);

        return $user;
    }
}
