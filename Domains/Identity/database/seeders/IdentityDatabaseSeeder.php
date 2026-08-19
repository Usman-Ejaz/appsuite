<?php

namespace Domains\Identity\Database\Seeders;

use Domains\Identity\Models\Company;
use Domains\Identity\Models\Role;
use Illuminate\Database\Seeder;

class IdentityDatabaseSeeder extends Seeder
{
    /**
     * The default roles created for every seeded company.
     */
    protected array $roles = ['Editor', 'Viewer', 'Member'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::factory()
            ->count(3)
            ->create()
            ->each(function (Company $company) {
                foreach ($this->roles as $name) {
                    Role::firstOrCreate([
                        'company_id' => $company->id,
                        'name' => $name,
                        'guard_name' => 'web',
                    ], [
                        'is_active' => true,
                    ]);
                }
            });
    }
}
