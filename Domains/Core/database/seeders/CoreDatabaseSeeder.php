<?php

namespace Domains\Core\Database\Seeders;

use Domains\Core\Enums\AppSiteMode;
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
        'cms' => ['name' => 'CMS', 'category' => 'Content', 'icon' => 'file-text'],
        'system' => ['name' => 'System', 'category' => 'Management', 'icon' => 'file-text'],
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
                'site_mode' => AppSiteMode::MULTI,
                'category' => $attributes['category'],
                'icon' => $attributes['icon'],
                'is_active' => true,
                'released_at' => now(),
            ]);
        }
    }
}
