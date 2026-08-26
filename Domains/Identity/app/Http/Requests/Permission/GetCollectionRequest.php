<?php

namespace Domains\Identity\Http\Requests\Permission;

use Domains\Core\Http\Requests\GetCollectionRequest as BaseGetCollectionRequest;

class GetCollectionRequest extends BaseGetCollectionRequest
{
    /**
     * Adds `app_id` on top of qubuilder's own params — GetCollectionRequest::filters()
     * only ever returns qubuilder's fixed select/filter/include/sort/group/page/limit
     * shape, which would otherwise silently drop this pre-existing convenience filter
     * that PermissionRepository::query() reads directly.
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'app_id' => ['sometimes', 'integer'],
        ];
    }

    protected function passedValidation(): void
    {
        parent::passedValidation();

        if ($this->filled('app_id')) {
            $this->filters['app_id'] = $this->integer('app_id');
        }
    }
}
