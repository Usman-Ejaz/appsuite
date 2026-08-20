<?php

namespace Domains\CMS\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormFieldResource extends BaseResource
{
    public string $routeName = 'api.cms.forms.fields';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'form_id' => $this->form_id,
            'label' => $this->label,
            'name' => $this->name,
            'type' => $this->type,
            'options' => $this->options,
            'default_value' => $this->default_value,
            'placeholder' => $this->placeholder,
            'help_text' => $this->help_text,
            'is_required' => $this->is_required,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'validation_rules' => $this->validation_rules,
        ]);
    }

    /**
     * BaseResource::getLinks() assumes a single `{id}` route parameter,
     * which doesn't fit this resource's `/forms/{form}/fields/{id}` nesting
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
