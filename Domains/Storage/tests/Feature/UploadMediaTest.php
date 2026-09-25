<?php

use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Storage\Actions\UploadMedia;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Folder;
use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * UploadMedia is a plain Action, not a trait — it works against any Eloquent model,
 * built directly here with a scratch table rather than requiring a resource to opt
 * into anything special (no HasMedia, no interface, nothing).
 */
uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Schema::create('mediable_test_models', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('company_id')->nullable();
        $table->string('slug')->nullable();
        $table->timestamps();
    });

    $this->company = Company::factory()->create();
    Sanctum::actingAs(User::factory()->create(['company_id' => $this->company->id]));
});

function mediableTestModel(?string $slug = null): Model
{
    $model = new class extends Model
    {
        protected $table = 'mediable_test_models';

        protected $guarded = [];
    };

    return $model::create(['slug' => $slug, 'company_id' => auth()->user()->company_id]);
}

test('it uploads a file and attaches it to the model', function () {
    Storage::fake('public');

    $model = mediableTestModel('widget');
    $file = UploadedFile::fake()->image('logo.png', 10, 10);

    $media = app(UploadMedia::class)->handle($model, $file, ['disk' => 'public']);

    expect($media)->toBeInstanceOf(Media::class)
        ->and($media->disk)->toBe('public')
        ->and($media->file_name)->toBe('logo.png')
        ->and($media->resource_id)->toBe($model->id)
        ->and($media->resource_type)->toBe($model->getMorphClass());

    Storage::disk('public')->assertExists($media->path);

    $this->assertDatabaseHas('media', [
        'id' => $media->id,
        'resource_id' => $model->id,
        'resource_type' => $model->getMorphClass(),
    ]);
});

test('it uploads a file downloaded from a url', function () {
    Storage::fake('public');
    Http::fake([
        'example.com/*' => Http::response('fake-file-contents', 200, ['Content-Type' => 'image/png']),
    ]);

    $model = mediableTestModel();

    $media = app(UploadMedia::class)->handleFromUrl($model, 'https://example.com/remote-logo.png', ['disk' => 'public']);

    expect($media->file_name)->toBe('remote-logo.png')
        ->and($media->mime_type)->toBe('image/png');
    Storage::disk('public')->assertExists($media->path);
});

test('it defaults to no category when none is given', function () {
    Storage::fake('public');

    $model = mediableTestModel();
    $media = app(UploadMedia::class)->handle($model, UploadedFile::fake()->image('logo.png'), ['disk' => 'public']);

    expect($media->category)->toBeNull();
});

test('it stores an upload under a given category', function () {
    Storage::fake('public');

    $model = mediableTestModel();
    $file = UploadedFile::fake()->image('avatar.png');

    $media = app(UploadMedia::class)->handle($model, $file, ['disk' => 'public', 'category' => MediaCategory::AVATAR]);

    expect($media->category)->toBe(MediaCategory::AVATAR);
});

test('it rejects a file whose extension is not allowed for the given category', function () {
    Storage::fake('public');

    $model = mediableTestModel();
    $file = UploadedFile::fake()->create('spec-sheet.pdf', 100);

    app(UploadMedia::class)->handle($model, $file, ['disk' => 'public', 'category' => MediaCategory::AVATAR]);
})->throws(InvalidArgumentException::class);

test('it rejects a file larger than the given category allows', function () {
    Storage::fake('public');

    $model = mediableTestModel();
    $file = UploadedFile::fake()->image('avatar.png')->size(MediaCategory::AVATAR->maxSizeInKilobytes() + 1);

    app(UploadMedia::class)->handle($model, $file, ['disk' => 'public', 'category' => MediaCategory::AVATAR]);
})->throws(InvalidArgumentException::class);

test('it stores an upload under a given title, starred flag, and folder', function () {
    Storage::fake('public');

    $folder = Folder::factory()->create();
    $model = mediableTestModel();
    $file = UploadedFile::fake()->image('IMG_1234.png');

    $media = app(UploadMedia::class)->handle($model, $file, [
        'disk' => 'public',
        'title' => 'Company Logo',
        'starred' => true,
        'folder_id' => $folder->id,
    ]);

    expect($media->file_name)->toBe('IMG_1234.png')
        ->and($media->title)->toBe('Company Logo')
        ->and($media->starred)->toBeTrue()
        ->and($media->folder_id)->toBe($folder->id);
});

test('the stored path is scoped by company, resource, and category, falling back to ids when there is no slug', function () {
    Storage::fake('public');

    $model = mediableTestModel('widget');
    $media = app(UploadMedia::class)->handle($model, UploadedFile::fake()->image('logo.png'), [
        'disk' => 'public',
        'category' => MediaCategory::GALLERY,
    ]);

    // No `company()` relation on this plain model, so the directory falls back to
    // "company_{id}" rather than a real slug — still scoped, just less readable.
    expect($media->path)->toStartWith("company_{$this->company->id}/widget/gallery/");
});

test('two uploads for different models are scoped independently', function () {
    Storage::fake('public');

    $first = mediableTestModel('first');
    $second = mediableTestModel('second');

    $firstMedia = app(UploadMedia::class)->handle($first, UploadedFile::fake()->image('one.png'), ['disk' => 'public']);
    $secondMedia = app(UploadMedia::class)->handle($second, UploadedFile::fake()->image('two.png'), ['disk' => 'public']);

    expect($firstMedia->resource_id)->toBe($first->id)
        ->and($secondMedia->resource_id)->toBe($second->id);
});
