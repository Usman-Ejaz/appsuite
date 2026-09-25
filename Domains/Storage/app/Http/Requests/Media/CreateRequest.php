<?php

namespace Domains\Storage\Http\Requests\Media;

use Closure;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Folder;
use Domains\Storage\Traits\HasMedia;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = MediaCategory::tryFrom((string) $this->input('category'));

        return [
            /**
             * The kind of resource this media item is attached to.
             *
             * @example product
             */
            'resource_type' => [
                'required',
                Rule::in(array_keys(Relation::morphMap())),
                function (string $attribute, mixed $value, Closure $fail) {
                    $modelClass = Relation::getMorphedModel((string) $value);

                    if (! $modelClass || ! in_array(HasMedia::class, class_uses_recursive($modelClass), true)) {
                        $fail('The selected resource type does not support media.');
                    }
                },
            ],

            /**
             * The id of the resource this media item is attached to. Scoped to the
             * authenticated company for company-scoped resources; global resources
             * (e.g. an App) are only checked for existence.
             *
             * @example 12
             */
            'resource_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) {
                    $modelClass = Relation::getMorphedModel((string) $this->input('resource_type'));

                    if (! $modelClass) {
                        return;
                    }

                    $query = $modelClass::query()->whereKey($value);

                    if (Schema::hasColumn((new $modelClass)->getTable(), 'company_id')) {
                        $query->where('company_id', $this->user()?->getCompanyId());
                    }

                    if (! $query->exists()) {
                        $fail('The selected resource does not exist.');
                    }
                },
            ],

            /**
             * The classification of this media item, which also determines the file
             * validation rules applied below. Restricted to whatever categories the
             * target resource declares via its allowedMediaCategories(), if overridden.
             *
             * @example Gallery
             */
            'category' => [
                'required',
                Rule::enum(MediaCategory::class),
                function (string $attribute, mixed $value, Closure $fail) {
                    $modelClass = Relation::getMorphedModel((string) $this->input('resource_type'));

                    if (! $modelClass) {
                        return;
                    }

                    $allowed = array_map(
                        fn (MediaCategory $case) => $case->value,
                        (new $modelClass)->allowedMediaCategories(),
                    );

                    if (! in_array($value, $allowed, true)) {
                        $fail('The selected category is not allowed for this resource.');
                    }
                },
            ],

            /**
             * The filesystem disk to store the uploaded files on. Defaults to the
             * application's default filesystem disk when omitted.
             *
             * @example public
             */
            'disk' => ['nullable', 'string', Rule::in(array_keys(config('filesystems.disks')))],

            /**
             * The library folder to file these media items under, scoped to the
             * authenticated company.
             *
             * @example 3
             */
            'folder_id' => ['nullable', 'integer', Rule::exists(Folder::class, 'id')->where('company_id', $this->user()?->getCompanyId())],

            /**
             * An optional title applied to every uploaded item.
             *
             * @example Product Hero Shot
             */
            'title' => ['nullable', 'string', 'max:255'],

            /**
             * Whether to mark the uploaded items as starred.
             *
             * @example false
             *
             * @default false
             */
            'starred' => ['nullable', 'boolean'],

            /**
             * One or more files to upload. At least one of `files`/`urls` is required.
             */
            'files' => ['required_without:urls', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:'.($category?->maxSizeInKilobytes() ?? 10_240),
                'mimes:'.implode(',', $category?->allowedExtensions() ?? MediaCategory::OTHER->allowedExtensions()),
            ],

            /**
             * One or more remote file URLs to download and attach. At least one of
             * `files`/`urls` is required.
             */
            'urls' => ['required_without:files', 'array', 'min:1'],
            'urls.*' => ['url'],
        ];
    }
}
