<?php

namespace Domains\Notification\Database\Seeders;

use Illuminate\Database\Seeder;

class NotificationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(NotificationPermissionSeeder::class);
    }
}
