<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Models\Role;
use Illuminate\Http\Request;

/**
 * @mixin Role
 */
class RoleResource extends BaseResource
{
    public string $routeName = 'api.identity.roles';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            /**
             * The company this role belongs to.
             */
            'company_id' => $this->whenHas('company_id'),

            /**
             * The role's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * The auth guard this role applies to.
             */
            'guard_name' => $this->whenHas('guard_name'),

            /**
             * Whether the role is currently assignable.
             */
            'is_active' => $this->whenHas('is_active'),

            /**
             * The permissions this role grants.
             *
             * @var PermissionResource[]
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
