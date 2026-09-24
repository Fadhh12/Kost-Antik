<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            InstanceSeeder::class,
            FacilitySeeder::class,
            UserSeeder::class,
            PropertySeeder::class,
            LeaseSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
