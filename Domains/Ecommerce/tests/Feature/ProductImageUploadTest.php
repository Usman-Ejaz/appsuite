<?php

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Product;
use Domains\Storage\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Product images are not uploaded through a Product-specific endpoint — there is no
 * ProductController::uploadMedia, no dedicated FormRequest, no ProductRepository method
 * for it. Products just declare category-filtered relations (images(), documents(),
 * thumbnail(), banner(), see Domains\Shared\Models\Product) and accept media through the
 * generic Domains\Storage media API instead:
 *   POST /api/v1/media  { resource_type: "product", resource_id: <id>, ... }
 * These tests exercise that exact flow against a real Product.
 */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->ecommerceApp = App::factory()->create(['code' => 'ecommerce']);
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $this->company->apps()->attach($this->ecommerceApp->id, ['assigned_by' => $this->user->id, 'assigned_at' => now()]);

    foreach (['ecommerce:products:view', 'ecommerce:products:create'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->ecommerceApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);

    $this->product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id]);
});

/**
 * Qubuilder's `include` query param must be a JSON array of include definitions,
 * e.g. `[{"name":"images"}]` — a plain `?include=images` string fails validation.
 */
function productUrlWithImagesIncluded(int $productId): string
{
    return '/api/v1/ecommerce/products/'.$productId.'?'.http_build_query([
        'include' => json_encode([['name' => 'images']]),
    ]);
}

test('uploading a product image via the generic media endpoint attaches it to the product', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('product.png')],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.0.resource_type', 'product')
        ->assertJsonPath('data.0.resource_id', $this->product->id)
        ->assertJsonPath('data.0.category', 'Gallery')
        ->assertJsonPath('data.0.file_name', 'product.png');

    expect($response->json('data.0.url'))->not->toBeNull();

    $this->assertDatabaseHas('media', [
        'resource_id' => $this->product->id,
        'resource_type' => 'product',
        'disk' => 'public',
        'category' => 'Gallery',
    ]);

    $media = Media::findOrFail($response->json('data.0.id'));
    Storage::disk('public')->assertExists($media->path);
});

test('the stored file path follows company_slug/resource_slug/category', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('product.png')],
    ]);

    $media = Media::findOrFail($response->json('data.0.id'));

    expect($media->path)->toStartWith("{$this->company->slug}/{$this->product->slug}/gallery/");
});

test('a product with uploaded images returns them nested when included', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('front.png')],
    ])->assertCreated();

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('back.png')],
    ])->assertCreated();

    $response = $this->getJson(productUrlWithImagesIncluded($this->product->id));

    $response->assertOk()->assertJsonCount(2, 'data.images');

    $names = collect($response->json('data.images'))->pluck('file_name');
    expect($names)->toContain('front.png', 'back.png');
});

test('a product with no uploaded images returns an empty media list when included', function () {
    $this->getJson(productUrlWithImagesIncluded($this->product->id))
        ->assertOk()
        ->assertJsonCount(0, 'data.images');
});

test('a product image is not returned in the media list without the include', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertCreated();

    $this->getJson("/api/v1/ecommerce/products/{$this->product->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.images');
});

test('uploading a product image accepts an optional title and starred flag', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'title' => 'Hero Shot',
        'starred' => true,
        'files' => [UploadedFile::fake()->image('IMG_00231.png')],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.0.title', 'Hero Shot')
        ->assertJsonPath('data.0.starred', true)
        ->assertJsonPath('data.0.file_name', 'IMG_00231.png');
});

test('a product can have a thumbnail and a banner uploaded independently', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Thumbnail',
        'files' => [UploadedFile::fake()->image('thumb.png')],
    ])->assertCreated();

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Banner',
        'files' => [UploadedFile::fake()->image('banner.png')],
    ])->assertCreated();

    $this->product->refresh();

    expect($this->product->thumbnail?->file_name)->toBe('thumb.png')
        ->and($this->product->banner?->file_name)->toBe('banner.png');
});

test('a category not allowed for products is rejected', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Avatar',
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('a non-image file is rejected for the Gallery category', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->create('spec-sheet.pdf', 100)],
    ])->assertUnprocessable();
});

test('uploading a product image requires a category', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('a product image cannot be uploaded against a product belonging to another company', function () {
    Storage::fake('public');

    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->ecommerce()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $otherProduct->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('deleting a product image removes it from the product and disk', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->image('product.png')],
    ]);

    $id = $response->json('data.0.id');
    $media = Media::findOrFail($id);

    $this->deleteJson("/api/v1/media/{$id}")->assertNoContent();

    $this->assertDatabaseMissing('media', ['id' => $id]);
    Storage::disk('public')->assertMissing($media->path);

    $this->getJson(productUrlWithImagesIncluded($this->product->id))
        ->assertOk()
        ->assertJsonCount(0, 'data.images');
});
