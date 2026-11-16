<?php

namespace Domains\Notification\Database\Seeders;

use BackedEnum;
use Domains\Notification\Models\Trigger;
use Illuminate\Database\Seeder;

/**
 * Seeds one domain's trigger enum into the `triggers` table. Safe to re-run: existing triggers
 * are updated in place, so the actions companies attached to them are kept.
 *
 * Each domain extends this with its own enum, e.g. `EcommerceTriggerSeeder`.
 */
abstract class BaseTriggerSeeder extends Seeder
{
    /**
     * The domain's trigger enum. Its cases are `Domain:Module:Event` codes with `label()` and
     * `description()` methods.
     *
     * @return class-string<BackedEnum>
     */
    abstract protected function triggers(): string;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->triggers()::cases() as $trigger) {
            [$domain, $module] = explode(':', $trigger->value);

            Trigger::updateOrCreate(['code' => $trigger->value], [
                'name' => $trigger?->label(),
                'description' => $trigger?->description(),
                'domain' => $domain,
                'module' => $module,
                'app_code' => $trigger?->app_code(),
            ]);
        }
    }
}
