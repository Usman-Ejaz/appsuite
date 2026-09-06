<?php

namespace Domains\Identity\Http\Resources;

use Domains\Identity\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApiKey
 */
class ApiKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            /**
             * The label given to the key when it was created.
             */
            'name' => $this->whenHas('name'),

            /**
             * The public identifier of the key, used together with the key's secret to
             * authenticate a request. The secret itself is only ever returned once, at
             * creation time.
             */
            'api_key' => $this->whenHas('api_key'),

            /**
             * The scopes this key is allowed to act with. `null` means the key carries every
             * ability its company can grant.
             */
            'abilities' => $this->whenHas('abilities'),

            /**
             * When this key was last used to authenticate a request. `null` if it has never
             * been used.
             */
            'last_used_at' => $this->whenHas('last_used_at'),

            /**
             * When this key stops being valid. `null` if it never expires.
             */
            'expires_at' => $this->whenHas('expires_at'),
            'created_at' => $this->whenHas('created_at'),
        ];
    }
}
