<?php

namespace Domains\Core\Database\Seeders;

use Domains\Core\Enums\SiteMode;
use Domains\Core\Models\App;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{
    /**
     * The suite apps to seed, keyed by code.
     */
    protected array $apps = [
        'crm' => ['name' => 'CRM', 'category' => 'Sales', 'icon' => 'users', 'site_mode' => null],
        'hr' => ['name' => 'HR', 'category' => 'People', 'icon' => 'briefcase', 'site_mode' => null],
        'finance' => ['name' => 'Finance', 'category' => 'Accounting', 'icon' => 'wallet', 'site_mode' => null],
        'inventory' => ['name' => 'Inventory', 'category' => 'Operations', 'icon' => 'box', 'site_mode' => null],
        'support' => ['name' => 'Support', 'category' => 'Service', 'icon' => 'life-buoy', 'site_mode' => null],
        'ecommerce' => ['name' => 'Ecommerce', 'category' => 'Commerce', 'icon' => 'shopping-cart', 'site_mode' => SiteMode::MULTI],
        'cms' => ['name' => 'CMS', 'category' => 'Content', 'icon' => 'file-text', 'site_mode' => SiteMode::MULTI],
        'system' => ['name' => 'System', 'category' => 'Management', 'icon' => 'file-text', 'site_mode' => null],
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
                'site_mode' => $attributes['site_mode'],
                'category' => $attributes['category'],
                'icon' => $attributes['icon'],
                'is_active' => true,
                'released_at' => now(),
            ]);
        }
    }
}
