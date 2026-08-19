<?php

namespace Domains\Core\Database\Seeders;

use Domains\Core\Models\App;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * The suite apps to seed, keyed by code.
     */
    protected array $apps = [
        'crm' => ['name' => 'CRM', 'category' => 'Sales', 'icon' => 'users'],
        'hr' => ['name' => 'HR', 'category' => 'People', 'icon' => 'briefcase'],
        'finance' => ['name' => 'Finance', 'category' => 'Accounting', 'icon' => 'wallet'],
        'inventory' => ['name' => 'Inventory', 'category' => 'Operations', 'icon' => 'box'],
        'support' => ['name' => 'Support', 'category' => 'Service', 'icon' => 'life-buoy'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->apps as $code => $attributes) {
            $app = App::firstOrCreate(['code' => $code], [
                'name' => $attributes['name'],
                'label' => $attributes['name'],
                'slug' => str($attributes['name'])->slug(),
                'category' => $attributes['category'],
                'icon' => $attributes['icon'],
                'is_active' => true,
                'released_at' => now(),
            ]);
        }
    }
}
