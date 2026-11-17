<?php

namespace Domains\Notification\Database\Seeders;

use BackedEnum;
use Domains\Core\Enums\AppCode;
use Domains\Notification\Models\Trigger;
use Illuminate\Database\Seeder;

/**
 * Seeds one domain's triggers into the `triggers` table. Safe to re-run: existing triggers are
 * updated in place, so the actions companies attached to them are kept.
 *
 * Each domain extends this with its `appCode()` (the app its triggers belong to) and its
 * `data()` (the label and description shown to companies for each trigger code), e.g.
 * `EcommerceTriggerSeeder`.
 */
abstract class BaseTriggerSeeder extends Seeder
{
    /**
     * The app every trigger of the domain belongs to.
     */
    abstract protected function appCode(): AppCode;

    /**
     * Every trigger of the domain, each listed once, with the label and description companies
     * see when they attach actions to it.
     *
     * @return list<array{trigger: BackedEnum, label: string, description: string}>
     */
    abstract protected function data(): array;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appCode = $this->appCode()->value;

        $rows = $this->data();

        foreach ($rows as $row) {
            $trigger = $row['trigger'];

            [$domain, $module] = explode(':', (string) $trigger->value);

            Trigger::updateOrCreate(['code' => $trigger->value], [
                'name' => $row['label'],
                'description' => $row['description'],
                'domain' => $domain,
                'module' => $module,
                'app_code' => $appCode,
            ]);
        }
    }
}
