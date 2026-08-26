<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Models\User;
use Illuminate\Http\Request;

/**
 * @mixin User
 */
class UserResource extends BaseResource
{
    public string $routeName = 'api.identity.users';

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            /**
             * The user's full name.
             */
            'name' => $this->name,

            /**
             * The user's email address.
             */
            'email' => $this->email,

            /**
             * The user's phone number.
             */
            'phone' => $this->phone,

            /**
             * The user's WhatsApp number.
             */
            'whatsapp' => $this->whatsapp,

            /**
             * The company this user belongs to.
             */
            'company_id' => $this->company_id,

            /**
             * Whether the user has unrestricted root access across the platform.
             */
            'is_root' => $this->is_root,

            /**
             * Whether the user is an owner of their company.
             */
            'is_owner' => $this->is_owner,
        ]);
    }
}
