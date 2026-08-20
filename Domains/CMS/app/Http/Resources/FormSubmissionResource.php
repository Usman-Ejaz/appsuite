<?php

namespace Domains\CMS\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FormSubmissionResource extends BaseResource
{
    public string $routeName = 'api.cms.forms.submissions';

    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'form_id' => $this->form_id,
            'data' => $this->data,
            'status' => $this->status,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'referrer_url' => $this->referrer_url,
        ]);
    }

    /**
     * BaseResource::getLinks() assumes a single `{id}` route parameter,
     * which doesn't fit this resource's `/forms/{form}/submissions/{id}`
     * nesting — override to pass both route parameters.
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
