<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Core\Enums\AppSiteMode;
use Domains\Core\Models\App;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class EcommerceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ecommerceApp = App::firstOrCreate(['code' => 'ecommerce'], [
            'name' => 'Ecommerce',
            'label' => 'Ecommerce',
            'slug' => 'ecommerce',
            'site_mode' => AppSiteMode::MULTI,
            'category' => 'Commerce',
            'icon' => 'shopping-cart',
            'is_active' => true,
            'released_at' => now(),
        ]);

        foreach (EcommercePermission::cases() as $permission) {
            Permission::firstOrCreate(
                ['app_id' => $ecommerceApp->id, 'name' => $permission->value, 'guard_name' => 'web'],
                ['label' => $permission->label(), 'code' => $permission->code(), 'description' => $permission->description()],
            );
        }
    }
}
