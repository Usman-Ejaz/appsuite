<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Product;
use Domains\Storage\Actions\UploadMedia;
use Domains\Storage\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    Sanctum::actingAs($this->user);

    $this->product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id]);
});

test('a media item can be created, listed, updated, and deleted for a resource', function () {
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

    $id = $response->json('data.0.id');

    $this->getJson('/api/v1/media')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson("/api/v1/media/{$id}")->assertOk()->assertJsonPath('data.id', $id);

    $this->putJson("/api/v1/media/{$id}", ['category' => 'Banner', 'title' => 'Renamed'])
        ->assertOk()
        ->assertJsonPath('data.category', 'Banner')
        ->assertJsonPath('data.title', 'Renamed');

    $this->deleteJson("/api/v1/media/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('media', ['id' => $id]);
});

test('creating media accepts multiple files in one request', function () {
    Storage::fake('public');

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [
            UploadedFile::fake()->image('front.png'),
            UploadedFile::fake()->image('back.png'),
        ],
    ]);

    $response->assertCreated()->assertJsonCount(2, 'data');

    $names = collect($response->json('data'))->pluck('file_name');
    expect($names)->toContain('front.png', 'back.png');

    $this->assertDatabaseCount('media', 2);
});

test('creating media accepts an array of urls', function () {
    Storage::fake('public');
    Http::fake([
        'example.com/*' => Http::response('fake-file-contents', 200, ['Content-Type' => 'image/png']),
    ]);

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'urls' => [
            'https://example.com/one.png',
            'https://example.com/two.png',
        ],
    ]);

    $response->assertCreated()->assertJsonCount(2, 'data');
    $this->assertDatabaseCount('media', 2);
});

test('a batch containing an invalid file is rejected entirely, nothing is persisted', function () {
    Storage::fake('public');

    $goodFile = UploadedFile::fake()->image('good.png');
    $badFile = UploadedFile::fake()->create('bad.pdf', 100);

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [$goodFile, $badFile],
    ]);

    $response->assertUnprocessable();
    $this->assertDatabaseCount('media', 0);
});

test('creating media accepts a folder, title, and starred flag', function () {
    Storage::fake('public');

    $folder = Folder::factory()->create(['company_id' => $this->company->id]);

    $response = $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'folder_id' => $folder->id,
        'title' => 'Hero Shot',
        'starred' => true,
        'files' => [UploadedFile::fake()->image('product.png')],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.0.folder_id', $folder->id)
        ->assertJsonPath('data.0.title', 'Hero Shot')
        ->assertJsonPath('data.0.starred', true);

    $this->assertDatabaseHas('media', [
        'resource_id' => $this->product->id,
        'folder_id' => $folder->id,
        'title' => 'Hero Shot',
        'starred' => true,
    ]);
});

test('creating media rejects a folder_id belonging to another company', function () {
    Storage::fake('public');

    $otherCompany = Company::factory()->create();
    $otherFolder = Folder::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'folder_id' => $otherFolder->id,
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('creating media requires a valid resource_type', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'not-a-real-type',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('creating media rejects a resource_id that does not exist for the given type', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => 999999,
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('creating media rejects a resource belonging to another company', function () {
    Storage::fake('public');

    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->ecommerce()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $otherProduct->id,
        'files' => [UploadedFile::fake()->image('product.png')],
    ])->assertUnprocessable();
});

test('creating media requires at least one file or url', function () {
    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
    ])->assertUnprocessable();
});

test('creating media enforces per-category file validation', function () {
    Storage::fake('public');

    $this->postJson('/api/v1/media', [
        'resource_type' => 'product',
        'disk' => 'public',
        'resource_id' => $this->product->id,
        'category' => 'Gallery',
        'files' => [UploadedFile::fake()->create('document.pdf', 100)],
    ])->assertUnprocessable();
});

test('a media item belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $otherProduct = Product::factory()->ecommerce()->create(['company_id' => $otherCompany->id]);

    Sanctum::actingAs(User::factory()->create(['company_id' => $otherCompany->id, 'is_owner' => true]));
    Storage::fake('public');

    $media = app(UploadMedia::class)->handle($otherProduct, UploadedFile::fake()->image('product.png'), ['disk' => 'public']);

    Sanctum::actingAs($this->user);

    $this->getJson("/api/v1/media/{$media->id}")->assertNotFound();
    $this->putJson("/api/v1/media/{$media->id}", ['category' => 'Banner'])->assertNotFound();
    $this->deleteJson("/api/v1/media/{$media->id}")->assertNotFound();
});
