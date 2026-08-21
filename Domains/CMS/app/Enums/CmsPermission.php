<?php

namespace Domains\CMS\Enums;

enum CmsPermission: string
{
    case ViewBlogs = 'cms:blogs:view';
    case CreateBlogs = 'cms:blogs:create';
    case UpdateBlogs = 'cms:blogs:update';
    case DeleteBlogs = 'cms:blogs:delete';
    case PublishBlogs = 'cms:blogs:publish';

    case ViewForms = 'cms:forms:view';
    case CreateForms = 'cms:forms:create';
    case UpdateForms = 'cms:forms:update';
    case DeleteForms = 'cms:forms:delete';
    case SubmitForms = 'cms:forms:submit';

    case ViewSubmissions = 'cms:submissions:view';
    case UpdateSubmissions = 'cms:submissions:update';
    case DeleteSubmissions = 'cms:submissions:delete';

    case ViewCategories = 'cms:categories:view';
    case ManageCategories = 'cms:categories:manage';

    public function label(): string
    {
        return match ($this) {
            self::ViewBlogs => 'View Blogs',
            self::CreateBlogs => 'Create Blogs',
            self::UpdateBlogs => 'Update Blogs',
            self::DeleteBlogs => 'Delete Blogs',
            self::PublishBlogs => 'Publish Blogs',
            self::ViewForms => 'View Forms',
            self::CreateForms => 'Create Forms',
            self::UpdateForms => 'Update Forms',
            self::DeleteForms => 'Delete Forms',
            self::SubmitForms => 'Submit Forms',
            self::ViewSubmissions => 'View Form Submissions',
            self::UpdateSubmissions => 'Update Form Submissions',
            self::DeleteSubmissions => 'Delete Form Submissions',
            self::ViewCategories => 'View Categories',
            self::ManageCategories => 'Manage Categories',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::ViewBlogs => 'blogs_view',
            self::CreateBlogs => 'blogs_create',
            self::UpdateBlogs => 'blogs_update',
            self::DeleteBlogs => 'blogs_delete',
            self::PublishBlogs => 'blogs_publish',
            self::ViewForms => 'forms_view',
            self::CreateForms => 'forms_create',
            self::UpdateForms => 'forms_update',
            self::DeleteForms => 'forms_delete',
            self::SubmitForms => 'forms_submit',
            self::ViewSubmissions => 'submissions_view',
            self::UpdateSubmissions => 'submissions_update',
            self::DeleteSubmissions => 'submissions_delete',
            self::ViewCategories => 'categories_view',
            self::ManageCategories => 'categories_manage',
        };
    }
}
