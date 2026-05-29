<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('nama_cabang', 'Klinik Utama')->firstOrFail();

        // 1. Default Super Admin (dari .env)
        $superAdmin = User::updateOrCreate(
            ['email' => $this->email()],
            [
                'name' => 'Super Admin',
                'whatsapp_number' => $this->whatsappNumber(),
                'password' => $this->password(),
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->assignRole('super_admin');

        // 1b. Developer (Mas Adam)
        $developer = User::firstOrCreate(
            ['email' => 'developer@acufara.com'],
            [
                'name' => 'Developer',
                'whatsapp_number' => '089999999999',
                'password' => bcrypt('password'),
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ]
        );
        $developer->assignRole('super_admin');
        $developer->assignRole('developer');

        // 2. Demo Superadmin (Read-only)
        $demoAdmin = User::firstOrCreate(
            ['email' => 'demo@acufara.com'],
            [
                'name' => 'Demo Superadmin',
                'whatsapp_number' => '081234567890',
                'password' => bcrypt('password'),
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ]
        );
        $demoAdmin->assignRole('demo_super_admin');

        // 3. Demo Patient
        $demoPatient = User::firstOrCreate(
            ['whatsapp_number' => '08111111111'],
            [
                'name' => 'Demo Patient',
                'password' => null,
                'branch_id' => null,
                'email' => 'patient@acufara.com',
                'email_verified_at' => now(),
            ]
        );
        $demoPatient->assignRole('patient');
    }

    private function email(): string
    {
        return (string) config('acufara.super_admin.email');
    }

    private function password(): string
    {
        return (string) config('acufara.super_admin.password');
    }

    private function whatsappNumber(): string
    {
        return (string) config('acufara.super_admin.whatsapp_number');
    }
}
