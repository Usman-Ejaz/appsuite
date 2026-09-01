<?php

namespace Domains\Shared\Http\Requests\Category;

use Domains\Shared\Enums\CategoryStatus;
use Domains\Shared\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appCode = $this->route('app_code');

        return Gate::allows('permission', "{$appCode}:categories:manage");
    }

    public function rules(): array
    {
        $companyId = $this->user()?->getCompanyId();
        $appCode = $this->route('app_code');

        return [
            /**
             * The category's display name.
             *
             * @example Electronics
             */
            'name' => ['sometimes', 'string', 'max:255'],

            /**
             * A url-friendly identifier for the category, unique per company.
             *
             * @example electronics
             */
            'slug' => [
                'sometimes', 'string', 'max:255',
                Rule::unique(Category::class, 'slug')->where('company_id', $companyId)->where('app_code', $appCode)->ignore($this->route('id')),
            ],

            /**
             * A short description of the category.
             *
             * @example Electronic devices and accessories.
             */
            'description' => ['nullable', 'string'],

            /**
             * Whether the category should be highlighted as featured.
             *
             * @example false
             */
            'is_featured' => ['nullable', 'boolean'],

            /**
             * The category's stage in its publishing lifecycle.
             *
             * @example Active
             */
            'status' => ['nullable', Rule::enum(CategoryStatus::class)],

            /**
             * The parent category this category belongs to, if any. Scoped to the same app.
             * A category cannot be made its own parent.
             *
             * @example 3
             */
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists(Category::class, 'id')->where('company_id', $companyId)->where('app_code', $appCode),
                Rule::notIn([$this->route('id')]),
            ],
        ];
    }
}
