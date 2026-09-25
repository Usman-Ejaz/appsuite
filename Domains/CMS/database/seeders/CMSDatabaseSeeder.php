<?php

namespace Domains\CMS\Database\Seeders;

use Domains\CMS\Enums\CmsPermission;
use Domains\Core\Enums\AppSiteMode;
use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class CMSDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cmsApp = App::firstOrCreate(['code' => 'cms'], [
            'name' => 'CMS',
            'label' => 'CMS',
            'slug' => 'cms',
            'site_mode' => AppSiteMode::MULTI,
            'category' => 'Content',
            'icon' => 'file-text',
            'is_active' => true,
            'released_at' => now(),
        ]);

        foreach (CmsPermission::cases() as $permission) {
            Permission::firstOrCreate(
                ['app_id' => $cmsApp->id, 'name' => $permission->value, 'guard_name' => 'web'],
                ['label' => $permission->label(), 'code' => $permission->code()],
            );
        }
    }
}
