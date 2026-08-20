<?php

use Domains\CMS\Models\Blog;
use Domains\Core\Models\App;
use Domains\Identity\Models\ApiKey;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\Permission;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->cmsApp = App::factory()->create(['code' => 'cms']);
    $this->company = Company::factory()->create();
    $this->company->apps()->attach($this->cmsApp->id);
    $this->user = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);

    foreach (['cms:blogs:view', 'cms:blogs:create', 'cms:blogs:update', 'cms:blogs:delete', 'cms:blogs:publish'] as $name) {
        $permission = Permission::create([
            'app_id' => $this->cmsApp->id,
            'name' => $name,
            'code' => str($name)->afterLast(':').'_test',
            'guard_name' => 'web',
        ]);
        $this->user->givePermissionTo($permission);
    }

    Sanctum::actingAs($this->user);
});

test('a blog can be created, listed, updated, and deleted', function () {
    $response = $this->postJson('/api/v1/blogs', ['title' => 'Hello World', 'slug' => 'hello-world']);
    $response->assertCreated()->assertJsonPath('data.title', 'Hello World');

    $id = $response->json('data.id');

    $this->getJson('/api/v1/blogs')->assertOk()->assertJsonCount(1, 'data');

    $this->putJson("/api/v1/blogs/{$id}", ['title' => 'Updated Title'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    $this->deleteJson("/api/v1/blogs/{$id}")->assertNoContent();
    $this->assertDatabaseMissing('blogs', ['id' => $id]);
});

test('creating a blog requires a title and slug', function () {
    $this->postJson('/api/v1/blogs', [])->assertUnprocessable();
});

test('assigning another company\'s author or category is rejected', function () {
    $otherCompany = Company::factory()->create();
    $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
    $otherCategory = Category::factory()->create(['company_id' => $otherCompany->id]);

    $this->postJson('/api/v1/blogs', [
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'author_id' => $otherUser->id,
        'category_id' => $otherCategory->id,
    ])->assertUnprocessable();
});

test('a blog belonging to a different company is not found', function () {
    $otherCompany = Company::factory()->create();
    $blog = Blog::factory()->create(['company_id' => $otherCompany->id]);

    $this->getJson("/api/v1/blogs/{$blog->id}")->assertNotFound();
    $this->putJson("/api/v1/blogs/{$blog->id}", ['title' => 'Hijacked'])->assertNotFound();
    $this->deleteJson("/api/v1/blogs/{$blog->id}")->assertNotFound();
});

test('a user without permission cannot manage blogs', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    Sanctum::actingAs($unprivilegedUser);

    $this->postJson('/api/v1/blogs', ['title' => 'Hello World', 'slug' => 'hello-world'])->assertForbidden();
});

test('publishing a blog sets published_at once and republishing does not reset it', function () {
    $blog = Blog::factory()->create(['company_id' => $this->company->id]);

    $this->postJson("/api/v1/blogs/{$blog->id}/publish")->assertOk();
    $firstPublishedAt = $blog->fresh()->published_at;

    expect($firstPublishedAt)->not->toBeNull();

    $this->postJson("/api/v1/blogs/{$blog->id}/unpublish")->assertOk();
    $this->postJson("/api/v1/blogs/{$blog->id}/publish")->assertOk();

    expect($blog->fresh()->published_at->equalTo($firstPublishedAt))->toBeTrue();
});

test('a user without the publish permission cannot publish a blog', function () {
    $unprivilegedUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    Sanctum::actingAs($unprivilegedUser);

    $blog = Blog::factory()->create(['company_id' => $this->company->id]);

    $this->postJson("/api/v1/blogs/{$blog->id}/publish")->assertForbidden();
});

test('slug uniqueness is scoped per company', function () {
    Blog::factory()->create(['company_id' => $this->company->id, 'slug' => 'hello-world']);

    $this->postJson('/api/v1/blogs', ['title' => 'Another', 'slug' => 'hello-world'])
        ->assertUnprocessable();
});

test('an api key with only the view ability never sees draft blogs, unlike a user with the same ability', function () {
    Blog::factory()->create(['company_id' => $this->company->id, 'status' => 'draft']);
    Blog::factory()->create(['company_id' => $this->company->id, 'status' => 'published', 'published_at' => now()->subDay()]);

    $viewOnlyUser = User::factory()->create(['company_id' => $this->company->id, 'is_owner' => true]);
    $viewPermission = Permission::create([
        'app_id' => $this->cmsApp->id,
        'name' => 'cms:blogs:view',
        'code' => 'blogs_view_only',
        'guard_name' => 'web',
    ]);
    $viewOnlyUser->givePermissionTo($viewPermission);

    Sanctum::actingAs($viewOnlyUser);
    $this->getJson('/api/v1/blogs')->assertOk()->assertJsonCount(1, 'data');

    $apiKey = ApiKey::factory()->create(['company_id' => $this->company->id, 'abilities' => ['cms:blogs:view']]);
    Sanctum::actingAs($apiKey);
    $this->getJson('/api/v1/blogs')->assertOk()->assertJsonCount(1, 'data');
});

test('author and category are nested when loaded', function () {
    $author = User::factory()->create(['company_id' => $this->company->id]);
    $category = Category::factory()->create(['company_id' => $this->company->id]);
    $blog = Blog::factory()->create([
        'company_id' => $this->company->id,
        'author_id' => $author->id,
        'category_id' => $category->id,
    ]);

    $this->getJson("/api/v1/blogs/{$blog->id}")
        ->assertOk()
        ->assertJsonPath('data.author.id', $author->id)
        ->assertJsonPath('data.category.id', $category->id);
});
