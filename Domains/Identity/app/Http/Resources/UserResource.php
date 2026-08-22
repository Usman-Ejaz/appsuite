<?php

namespace Domains\Identity\Http\Resources;

use Domains\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'company_id' => $this->company_id,
            'is_root' => $this->is_root,
            'is_owner' => $this->is_owner,
            'created_at' => $this->created_at,
        ];
    }
}
