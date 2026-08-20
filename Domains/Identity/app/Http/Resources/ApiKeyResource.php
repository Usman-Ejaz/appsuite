<?php

namespace Domains\Identity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'api_key' => $this->api_key,
            'abilities' => $this->abilities,
            'last_used_at' => $this->last_used_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
