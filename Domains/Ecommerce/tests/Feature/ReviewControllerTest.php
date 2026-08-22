<?php

use Domains\Core\Models\App;
use Domains\Ecommerce\Enums\ReviewStatus;
use Domains\Ecommerce\Models\EcommerceProduct;
use Domains\Ecommerce\Models\Review;
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

    $this->permissions = [];

    foreach ([
        'ecommerce:reviews:view', 'ecommerce:reviews:create', 'ecommerce:reviews:update',
        'ecommerce:reviews:delete', 'ecommerce:reviews:moderate',
    ] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->permissions[$name] = $permission;
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);

    $this->product = EcommerceProduct::factory()->create(['company_id' => $this->company->id]);
});

test('a review can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/ecommerce/reviews', [
        'ecommerce_product_id' => $this->product->id,
        'rating' => 5,
        'title' => 'Great product',
        'body' => 'Works exactly as described.',
    ]);
    $response->assertCreated()
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.title', 'Great product')
        ->assertJsonPath('data.status', 'Pending');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/ecommerce/reviews')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/ecommerce/reviews/{$id}", ['rating' => 4, 'title' => 'Updated title'])
        ->assertOk()
        ->assertJsonPath('data.rating', 4)
        ->assertJsonPath('data.title', 'Updated title');

    $this->deleteJson("/api/v1/ecommerce/reviews/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('reviews', ['id' => $id]);
});

test('creating a review requires a product and rating', function () {
    $this->postJson('/api/v1/ecommerce/reviews', [])->assertUnprocessable();
});

test('rating must be between 1 and 5', function (int $rating) {
    $this->postJson('/api/v1/ecommerce/reviews', [
        'ecommerce_product_id' => $this->product->id,
        'rating' => $rating,
    ])->assertUnprocessable();
})->with([0, 6]);

test('a newly created review always has a Pending status', function () {
    $response = $this->postJson('/api/v1/ecommerce/reviews', [
        'ecommerce_product_id' => $this->product->id,
        'rating' => 5,
        'status' => ReviewStatus::APPROVED->value,
    ]);

    $response->assertCreated()->assertJsonPath('data.status', ReviewStatus::PENDING->value);
});

test('a review belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $otherProduct = EcommerceProduct::factory()->create(['company_id' => $otherCompany->id]);
    $review = Review::factory()->create(['company_id' => $otherCompany->id, 'ecommerce_product_id' => $otherProduct->id]);

    $this->getJson("/api/v1/ecommerce/reviews/{$review->id}")->assertNotFound();
    $this->putJson("/api/v1/ecommerce/reviews/{$review->id}", ['rating' => 3])->assertNotFound();
    $this->deleteJson("/api/v1/ecommerce/reviews/{$review->id}")->assertNotFound();
});

test('a user without permission cannot manage reviews', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    $unprivilegedUser->apps()->attach($this->ecommerceApp->id);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/ecommerce/reviews', [
        'ecommerce_product_id' => $this->product->id,
        'rating' => 5,
    ])->assertForbidden();
});

test('getting a single review includes the nested product', function () {
    $review = Review::factory()->create(['company_id' => $this->company->id, 'ecommerce_product_id' => $this->product->id]);

    $this->getJson("/api/v1/ecommerce/reviews/{$review->id}")
        ->assertOk()
        ->assertJsonPath('data.product.id', $this->product->id);
});

test('moderating a review to Approved succeeds', function () {
    $review = Review::factory()->create(['company_id' => $this->company->id, 'ecommerce_product_id' => $this->product->id]);

    $this->patchJson("/api/v1/ecommerce/reviews/{$review->id}/moderate", ['status' => 'Approved'])
        ->assertOk()
        ->assertJsonPath('data.status', 'Approved');

    $this->assertDatabaseHas('reviews', ['id' => $review->id, 'status' => 'Approved']);
});

test('moderating a review to Pending is rejected', function () {
    $review = Review::factory()->create(['company_id' => $this->company->id, 'ecommerce_product_id' => $this->product->id]);

    $this->patchJson("/api/v1/ecommerce/reviews/{$review->id}/moderate", ['status' => 'Pending'])
        ->assertUnprocessable();
});

test('a user with update permission but not moderate permission cannot moderate a review', function () {
    $review = Review::factory()->create(['company_id' => $this->company->id, 'ecommerce_product_id' => $this->product->id]);

    $updateOnlyUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => false]);
    $updateOnlyUser->apps()->attach($this->ecommerceApp->id);
    $updateOnlyUser->givePermissionTo($this->permissions['ecommerce:reviews:update']);
    Sanctum::actingAs($updateOnlyUser);

    $this->patchJson("/api/v1/ecommerce/reviews/{$review->id}/moderate", ['status' => 'Approved'])
        ->assertForbidden();
});
