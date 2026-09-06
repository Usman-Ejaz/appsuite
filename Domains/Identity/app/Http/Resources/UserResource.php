<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Http\Resources\AppResource;
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
        return [
            'id' => $this->id,

            /**
             * The user's full name.
             */
            'name' => $this->whenHas('name'),

            /**
             * The user's email address.
             */
            'email' => $this->whenHas('email'),

            /**
             * The user's phone number.
             */
            'phone' => $this->whenHas('phone'),

            /**
             * The user's WhatsApp number.
             */
            'whatsapp' => $this->whenHas('whatsapp'),

            /**
             * The company this user belongs to.
             */
            'company_id' => $this->whenHas('company_id'),

            /**
             * Whether the user has unrestricted root access across the platform.
             */
            'is_root' => $this->whenHas('is_root'),

            /**
             * Whether the user is an owner of their company.
             */
            'is_owner' => $this->whenHas('is_owner'),

            /**
             * The roles assigned to this user. Only present when eager-loaded, e.g. via
             * `?include=[{"name":"roles"}]`.
             *
             * @var RoleResource[]
             */
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            /**
             * The apps individually granted to this user, a subset of their company's
             * subscribed apps. Only present when eager-loaded, e.g. via
             * `?include=[{"name":"apps"}]`.
             *
             * @var AppResource[]
             */
            'apps' => AppResource::collection($this->whenLoaded('apps')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
