<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * @var list<array{name: string, base_price: int, is_active: bool}>
     */
    private const SERVICES = [
        [
            'name' => 'Akupunktur',
            'base_price' => 150000,
            'is_active' => true,
        ],
        [
            'name' => 'Bekam',
            'base_price' => 125000,
            'is_active' => true,
        ],
        [
            'name' => 'Baby Spa',
            'base_price' => 175000,
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::SERVICES as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                [
                    'base_price' => $service['base_price'],
                    'is_active' => $service['is_active'],
                ],
            );
        }
    }
}
