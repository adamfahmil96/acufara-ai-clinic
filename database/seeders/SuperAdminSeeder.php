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

        $user = User::updateOrCreate(
            ['email' => $this->email()],
            [
                'name' => 'Super Admin',
                'whatsapp_number' => $this->whatsappNumber(),
                'password' => $this->password(),
                'branch_id' => $branch->id,
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole('super_admin');
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
