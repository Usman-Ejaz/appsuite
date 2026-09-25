<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Product;
use Domains\Storage\Actions\UploadMediaBatch;
use Domains\Storage\Enums\MediaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create();
    Sanctum::actingAs(User::factory()->create(['company_id' => $this->company->id]));

    $this->product = Product::factory()->ecommerce()->create(['company_id' => $this->company->id]);
});

test('it uploads several files in one call', function () {
    Storage::fake('public');

    $media = app(UploadMediaBatch::class)->handle($this->product, [
        UploadedFile::fake()->image('front.png'),
        UploadedFile::fake()->image('back.png'),
    ], [], ['disk' => 'public', 'category' => MediaCategory::GALLERY]);

    expect($media)->toHaveCount(2);
    $this->assertDatabaseCount('media', 2);

    $media->each(fn ($item) => Storage::disk('public')->assertExists($item->path));
});

test('a mid-batch failure rolls back the DB rows and any files already stored on disk', function () {
    Storage::fake('public');

    $goodFile = UploadedFile::fake()->image('good.png');
    $badFile = UploadedFile::fake()->create('bad.pdf', 100);

    expect(fn () => app(UploadMediaBatch::class)->handle(
        $this->product,
        [$goodFile, $badFile],
        [],
        ['disk' => 'public', 'category' => MediaCategory::GALLERY],
    ))->toThrow(InvalidArgumentException::class);

    $this->assertDatabaseCount('media', 0);
    Storage::disk('public')->assertDirectoryEmpty($this->company->slug);
});
