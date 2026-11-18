<?php

namespace Domains\Identity\Database\Seeders;

use BackedEnum;
use Domains\Core\Enums\AppCode;
use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Seeds one domain's permissions into the `permissions` table, under the domain's app. Safe to
 * re-run: existing permissions are updated in place, so the roles they are assigned to are kept.
 *
 * Each domain extends this with its `appCode()` (the app its permissions belong to, which must
 * already exist) and its `data()` (the label and description shown to companies for each
 * permission), e.g. `EcommercePermissionSeeder`.
 */
abstract class BasePermissionSeeder extends Seeder
{
    /**
     * The app every permission of the domain belongs to.
     */
    abstract protected function appCode(): AppCode;

    /**
     * Every permission of the domain, each listed once, with the label and description companies
     * see when they assign it to a role.
     *
     * @return list<array{permission: BackedEnum, label: string, description: string}>
     */
    abstract protected function data(): array;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appId = App::where('code', $this->appCode()->value)->firstOrFail()->id;

        $rows = $this->data();

        foreach ($rows as $row) {
            $name = (string) $row['permission']->value;

            Permission::updateOrCreate([
                'app_id' => $appId,
                'name' => $name,
                'guard_name' => 'web',
            ], [
                'label' => $row['label'],
                'description' => $row['description'],
            ]);
        }
    }
}
