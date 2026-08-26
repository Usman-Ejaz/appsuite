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
        $this->loadMissing('permissions:id,name,label,code');

        return array_merge(parent::toArray($request), [
            /**
             * The company this role belongs to.
             */
            'company_id' => $this->company_id,

            /**
             * The role's display name.
             */
            'name' => $this->name,

            /**
             * The auth guard this role applies to.
             */
            'guard_name' => $this->guard_name,

            /**
             * Whether the role is currently assignable.
             */
            'is_active' => $this->is_active,

            /**
             * The permissions this role grants.
             *
             * @var PermissionResource[]
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ]);
    }
}
