<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['nama_cabang' => 'Klinik Utama'],
            [
                'alamat' => 'Jl. Klinik Utama No. 1',
                'is_active' => true,
            ],
        );
    }
}
