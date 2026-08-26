<?php

namespace Database\Seeders;

use Domains\CMS\Database\Seeders\CMSDatabaseSeeder;
use Domains\Core\Database\Seeders\CoreDatabaseSeeder;
use Domains\Ecommerce\Database\Seeders\EcommerceDatabaseSeeder;
use Domains\Identity\Database\Seeders\IdentityDatabaseSeeder;
use Domains\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'root@test.com'],
            [
                'name' => 'Root',
                'password' => Hash::make('password'),
                'is_root' => true,
            ],
        );

        $this->call([
            CoreDatabaseSeeder::class,
            CMSDatabaseSeeder::class,
            EcommerceDatabaseSeeder::class,
            IdentityDatabaseSeeder::class,
        ]);
    }
}
