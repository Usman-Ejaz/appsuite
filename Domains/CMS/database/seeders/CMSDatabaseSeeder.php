<?php

namespace Domains\CMS\Database\Seeders;

use Domains\Core\Models\App;
use Domains\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class CMSDatabaseSeeder extends Seeder
{
    /**
     * The `cms:{resource}:{action}` permissions to seed, keyed by `name`
     * with their `label`/`code`. Format per .ai/rules/models.md.
     */
    protected array $permissions = [
        'cms:blogs:view' => ['label' => 'View Blogs', 'code' => 'blogs_view'],
        'cms:blogs:create' => ['label' => 'Create Blogs', 'code' => 'blogs_create'],
        'cms:blogs:update' => ['label' => 'Update Blogs', 'code' => 'blogs_update'],
        'cms:blogs:delete' => ['label' => 'Delete Blogs', 'code' => 'blogs_delete'],
        'cms:blogs:publish' => ['label' => 'Publish Blogs', 'code' => 'blogs_publish'],
        'cms:forms:view' => ['label' => 'View Forms', 'code' => 'forms_view'],
        'cms:forms:create' => ['label' => 'Create Forms', 'code' => 'forms_create'],
        'cms:forms:update' => ['label' => 'Update Forms', 'code' => 'forms_update'],
        'cms:forms:delete' => ['label' => 'Delete Forms', 'code' => 'forms_delete'],
        'cms:forms:submit' => ['label' => 'Submit Forms', 'code' => 'forms_submit'],
        'cms:submissions:view' => ['label' => 'View Form Submissions', 'code' => 'submissions_view'],
        'cms:submissions:update' => ['label' => 'Update Form Submissions', 'code' => 'submissions_update'],
        'cms:submissions:delete' => ['label' => 'Delete Form Submissions', 'code' => 'submissions_delete'],
        'cms:categories:view' => ['label' => 'View Categories', 'code' => 'categories_view'],
        'cms:categories:manage' => ['label' => 'Manage Categories', 'code' => 'categories_manage'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cmsApp = App::firstOrCreate(['code' => 'cms'], [
            'name' => 'CMS',
            'label' => 'CMS',
            'slug' => 'cms',
            'category' => 'Content',
            'icon' => 'file-text',
            'is_active' => true,
            'released_at' => now(),
        ]);

        foreach ($this->permissions as $name => $attributes) {
            Permission::firstOrCreate(
                ['app_id' => $cmsApp->id, 'name' => $name, 'guard_name' => 'web'],
                ['label' => $attributes['label'], 'code' => $attributes['code']],
            );
        }
    }
}
