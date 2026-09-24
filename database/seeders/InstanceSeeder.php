<?php

namespace Database\Seeders;

use App\Enums\InstanceType;
use App\Models\Instance;
use Illuminate\Database\Seeder;

class InstanceSeeder extends Seeder
{
    public function run(): void
    {
        $instances = [
            ['President University', InstanceType::Campus, 'Jababeka Education Park, Cikarang'],
            ['Universitas Pelita Bangsa', InstanceType::Campus, 'Jl. Inspeksi Kalimalang, Cikarang'],
            ['Universitas Islam 45 Bekasi', InstanceType::Campus, 'Jl. Cut Meutia No. 83, Bekasi'],
            ['Universitas Bhayangkara Jakarta Raya', InstanceType::Campus, 'Jl. Perjuangan, Bekasi Utara'],
            ['Kawasan Industri Jababeka', InstanceType::Office, 'Cikarang Utara, Bekasi'],
            ['Kawasan Industri MM2100', InstanceType::Office, 'Cikarang Barat, Bekasi'],
        ];

        foreach ($instances as [$name, $type, $address]) {
            Instance::updateOrCreate(['name' => $name], ['type' => $type, 'address' => $address]);
        }
    }
}
