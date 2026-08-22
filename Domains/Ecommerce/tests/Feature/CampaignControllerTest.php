<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Models\Campaign;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:campaigns:view', 'ecommerce:campaigns:create', 'ecommerce:campaigns:update', 'ecommerce:campaigns:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);

    // Defensive belt-and-suspenders: Domains\Core\app\Traits\HasCompany's
    // CompanyScope is bound once per model class per process. At the time
    // these tests were written it was captured from request()->user() at
    // boot time, which is null when the model's first touch in a test is
    // a direct factory call rather than an actual HTTP dispatch - that
    // silently disables company scoping for the rest of the test. Wiring
    // the resolver here keeps it correct regardless of what touches the
    // model first. See bug note in the final report.
    request()->setUserResolver(fn () => Auth::user());
});

/**
 * Create a record that belongs to a company other than the currently
 * acting one. Acting as a user from the target company while creating the
 * fixture guarantees the correct company_id regardless of exactly how
 * Domains\Core\app\Traits\HasCompany derives it on creation - see bug
 * note in the final report for the specific issue this guarded against
 * at the time these tests were written.
 */
function createCampaignForCompany(Company $company, array $attributes = []): Campaign
{
    $owner = User::factory()->create(['company_id' => $company->id]);
    Sanctum::actingAs($owner);

    return Campaign::factory()->create(array_merge(['company_id' => $company->id], $attributes));
}

test('a campaign can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/campaigns', [
        'name' => 'Black Friday',
        'slug' => 'black-friday',
    ]);
    $response->assertCreated()->assertJsonPath('data.name', 'Black Friday');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/campaigns')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/campaigns/{$id}", ['name' => 'Cyber Monday'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Cyber Monday');

    $this->deleteJson("/api/v1/ecommerce/campaigns/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('campaigns', ['id' => $id]);
});

test('creating a campaign requires a name and slug', function () {
    $this->postJson('/api/v1/ecommerce/campaigns', [])->assertUnprocessable();
});

test('a campaign belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $campaign = createCampaignForCompany($otherCompany);
    Sanctum::actingAs($this->user);

    $this->getJson("/api/v1/ecommerce/campaigns/{$campaign->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/campaigns/{$campaign->id}", ['name' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/campaigns/{$campaign->id}")->assertNotFound();
});

test('a user without permission cannot manage campaigns', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/campaigns', ['name' => 'Black Friday', 'slug' => 'black-friday'])
        ->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    Campaign::factory()->create(['company_id' => $this->company->id, 'slug' => 'black-friday']);

    $this->postJson('/api/v1/ecommerce/campaigns', ['name' => 'Another', 'slug' => 'black-friday'])
        ->assertUnprocessable();
});

test('a campaign status defaults to Draft when omitted', function () {
    $response = $this->postJson('/api/v1/ecommerce/campaigns', [
        'name' => 'Black Friday',
        'slug' => 'black-friday',
    ]);

    $response->assertCreated()->assertJsonPath('data.status', 'Draft');
});

test('a campaign cannot end before it starts', function () {
    $this->postJson('/api/v1/ecommerce/campaigns', [
        'name' => 'Black Friday',
        'slug' => 'black-friday',
        'starts_at' => now()->addDays(5)->toDateTimeString(),
        'ends_at' => now()->addDays(1)->toDateTimeString(),
    ])->assertUnprocessable();
});
