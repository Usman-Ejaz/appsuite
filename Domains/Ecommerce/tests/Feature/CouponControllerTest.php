<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Enums\CouponType;
use Domains\Ecommerce\Models\Campaign;
use Domains\Ecommerce\Models\Coupon;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:coupons:view', 'ecommerce:coupons:create', 'ecommerce:coupons:update', 'ecommerce:coupons:delete'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
});

test('a coupon can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/coupons', [
        'code' => 'SAVE10',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ]);
    $response->assertCreated()->assertJsonPath('data.code', 'SAVE10');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/coupons')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/coupons/{$id}", ['code' => 'SAVE10-V2'])
        ->assertOk()
        ->assertJsonPath('data.code', 'SAVE10-V2');

    $this->deleteJson("/api/v1/ecommerce/coupons/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('coupons', ['id' => $id]);
});

test('creating a coupon requires a code, type, and value', function () {
    $this->postJson('/api/v1/ecommerce/coupons', [])->assertUnprocessable();
});

test('a coupon belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $coupon = Coupon::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/ecommerce/coupons/{$coupon->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/coupons/{$coupon->id}", ['code' => 'HIJACKED'])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/coupons/{$coupon->id}")->assertNotFound();
});

test('a user without permission cannot manage coupons', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/coupons', [
        'code' => 'NOPERM',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ])->assertForbidden();
});

test('coupon code uniqueness is scoped per company', function () {
    $otherCompany = Company::factory()->create();
    Coupon::factory()->create(['company_id' => $otherCompany->id, 'code' => 'SAVE10']);

    $this->postJson('/api/v1/ecommerce/coupons', [
        'code' => 'SAVE10',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ])->assertCreated();

    $this->postJson('/api/v1/ecommerce/coupons', [
        'code' => 'SAVE10',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ])->assertUnprocessable();
});

test('creating a coupon with a campaign succeeds and the campaign is eager loaded on get', function () {
    $campaign = Campaign::factory()->create(['company_id' => $this->company->id]);

    $response = $this->postJson('/api/v1/ecommerce/coupons', [
        'campaign_id' => $campaign->id,
        'code' => 'CAMP10',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ])->assertCreated();

    $id = $response->json('data.id');

    $this->getJson("/api/v1/ecommerce/coupons/{$id}")
        ->assertOk()
        ->assertJsonPath('data.campaign.id', $campaign->id);
});

test('creating a coupon with another company\'s campaign is rejected', function () {
    $otherCompany = Company::factory()->create();
    $otherCampaign = Campaign::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/ecommerce/coupons', [
        'campaign_id' => $otherCampaign->id,
        'code' => 'BADCAMP',
        'type' => CouponType::FIXED->value,
        'value' => 10,
    ])->assertUnprocessable();
});
