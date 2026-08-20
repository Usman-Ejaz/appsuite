<?php

namespace Domains\CMS\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class FormResource extends BaseResource
{
    public string $routeName = 'api.cms.forms';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'company_id' => $this->company_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'success_message' => $this->success_message,
            'redirect_url' => $this->redirect_url,
            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
            'actions' => FormActionResource::collection($this->whenLoaded('actions')),
        ]);
    }
}
