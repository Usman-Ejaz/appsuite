<?php

namespace Domains\CMS\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Shared\Models\Category;
use Illuminate\Http\Request;

/**
 * @mixin Category
 */
class CategoryResource extends BaseResource
{
    public string $routeName = 'api.cms.categories';

    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'company_id' => $this->whenHas('company_id'),
            'name' => $this->whenHas('name'),
            'slug' => $this->whenHas('slug'),
            'description' => $this->whenHas('description'),
            $this->merge(parent::toArray($request))
        ];
    }
}
