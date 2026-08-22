<?php

namespace Domains\CMS\Http\Resources;

use Domains\CMS\Models\FormAction;
use Domains\Core\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * @mixin FormAction
 */
class FormActionResource extends BaseResource
{
    public string $routeName = 'api.cms.forms.actions';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'form_id' => $this->form_id,
            'type' => $this->type,
            'name' => $this->name,
            'config' => $this->config,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);
    }

    /**
     * BaseResource::getLinks() assumes a single `{id}` route parameter,
     * which doesn't fit this resource's `/forms/{form}/actions/{id}` nesting
     * — override to pass both route parameters.
     */
    public function getLinks(): ?array
    {
        if (empty($this->routeName) || empty($this->id) || empty($this->form_id)) {
            return null;
        }

        return [
            'self' => Route::has("{$this->routeName}.get")
                ? route("{$this->routeName}.get", ['form' => $this->form_id, 'id' => $this->id])
                : null,
            'parent' => Route::has("{$this->routeName}.list")
                ? route("{$this->routeName}.list", ['form' => $this->form_id])
                : null,
        ];
    }
}
