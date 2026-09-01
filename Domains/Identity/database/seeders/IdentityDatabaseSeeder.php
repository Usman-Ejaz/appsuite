<?php

namespace Domains\Identity\Database\Seeders;

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Role;
use Domains\Identity\Models\User;
use Illuminate\Database\Seeder;

class IdentityDatabaseSeeder extends Seeder
{
    /**
     * The default roles created for every seeded company.
     */
    protected array $roles = ['Editor', 'Viewer', 'Member'];

    /**
     * The apps every seeded company is subscribed to (only the ones with a
     * real, working domain behind them today).
     */
    protected array $companyApps = ['cms', 'ecommerce', 'hr', 'crm', 'front-desk'];

    /**
     * How many regular (non-owner) users to seed per company, on top of the
     * one owner.
     */
    protected int $usersPerCompany = 4;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assignedBy = User::query()->where('is_root', true)->first();
        $appIds = App::query()->whereIn('code', $this->companyApps)->pluck('id');

        Company::factory()
            ->count(3)
            ->create()
            ->each(function (Company $company) use ($appIds, $assignedBy) {
                foreach ($this->roles as $name) {
                    Role::firstOrCreate([
                        'company_id' => $company->id,
                        'name' => $name,
                        'guard_name' => 'web',
                    ], [
                        'is_active' => true,
                    ]);
                }

                if ($assignedBy && $appIds->isNotEmpty()) {
                    $company->apps()->attach($appIds, [
                        'assigned_by' => $assignedBy->id,
                        'assigned_at' => now(),
                    ]);
                }

                User::factory()->create([
                    'name' => "{$company->name} Owner",
                    'email' => "owner@{$company->slug}.test",
                    'company_id' => $company->id,
                    'is_owner' => true,
                ]);

                User::factory()->count($this->usersPerCompany)->create([
                    'company_id' => $company->id,
                ]);
            });
    }
}
