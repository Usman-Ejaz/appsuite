<?php

namespace Domains\CMS\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class CategoryResource extends BaseResource
{
    public string $routeName = 'api.cms.categories';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ]);
    }
}
