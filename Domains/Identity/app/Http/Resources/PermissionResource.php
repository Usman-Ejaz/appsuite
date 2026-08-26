<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Identity\Models\Permission;
use Illuminate\Http\Request;

/**
 * @mixin Permission
 */
class PermissionResource extends BaseResource
{
    public string $routeName = 'api.identity.permissions';

    public function toArray(Request $request): array
    {
        return [
            /**
             * The app this permission belongs to.
             */
            'app_id' => $this->whenHas('app_id'),

            /**
             * The permission's unique name, used by the authorization gate.
             */
            'name' => $this->whenHas('name'),

            /**
             * A human-readable label for the permission.
             */
            'label' => $this->whenHas('label'),

            /**
             * A short description of what the permission grants.
             */
            'description' => $this->whenHas('description'),

            /**
             * A short code identifying the permission within its app.
             */
            'code' => $this->whenHas('code'),

            /**
             * The auth guard this permission applies to.
             */
            'guard_name' => $this->whenHas('guard_name'),

            ...parent::toArray($request),
        ];
    }
}
