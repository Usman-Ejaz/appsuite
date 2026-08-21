<?php

namespace Domains\CMS\Http\Requests\Blog;

use Domains\CMS\Enums\BlogStatus;
use Domains\CMS\Enums\CmsPermission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('permission', CmsPermission::UpdateBlogs->value);
    }

    public function rules(): array
    {
        $companyId = $this->user()->getCompanyId();

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('blogs')->where('company_id', $companyId)->ignore($this->route('id'))],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'status' => ['nullable', Rule::enum(BlogStatus::class)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
