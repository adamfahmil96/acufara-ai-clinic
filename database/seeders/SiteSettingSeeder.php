<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const SETTINGS = [
        'header.brand_name' => 'Acufara Klinik & Spa',
        'header.whatsapp_number' => '6281234567890',
        'hero.title' => 'Klinik Akupunktur, Bekam, dan Baby Spa',
        'hero.subtitle' => 'Perawatan holistik dengan alur booking yang mudah dan rekam medis digital.',
        'hero.cta_label' => 'Booking Sekarang',
        'content.about_title' => 'Perawatan nyaman untuk keluarga',
        'content.about_body' => 'Acufara membantu pasien mendapatkan layanan klinik maupun homecare dengan proses yang rapi.',
        'footer.address' => 'Jl. Klinik Utama No. 1',
        'footer.instagram' => 'https://instagram.com/acufara',
        'seo.meta_title' => 'Acufara Klinik & Spa',
        'seo.meta_description' => 'Layanan akupunktur, bekam, baby spa, dan homecare dengan booking online.',
    ];

    public function run(): void
    {
        foreach (self::SETTINGS as $key => $value) {
            SiteSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value],
            );
        }
    }
}
