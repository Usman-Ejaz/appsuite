<?php

namespace Domains\Notification\Database\Seeders;

use Domains\Core\Enums\SiteMode;
use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Domains\Notification\Enums\NotificationPermission;
use Illuminate\Database\Seeder;

class NotificationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemApp = App::firstOrCreate(['code' => 'system'], [
            'name' => 'System',
            'label' => 'System',
            'slug' => 'system',
            'site_mode' => SiteMode::MULTI,
            'category' => 'Management',
            'icon' => 'file-text',
            'is_active' => true,
            'released_at' => now(),
        ]);

        foreach (NotificationPermission::cases() as $permission) {
            Permission::firstOrCreate(
                ['app_id' => $systemApp->id, 'name' => $permission->label(), 'guard_name' => 'web'],
                ['code' => $permission->value, 'description' => $permission->description()],
            );
        }
    }
}
