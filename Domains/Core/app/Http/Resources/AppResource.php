<?php

namespace Domains\Core\Http\Resources;

use Domains\Core\Models\App;
use Domains\Identity\Http\Resources\PermissionResource;
use Illuminate\Http\Request;

/**
 * @mixin App
 */
class AppResource extends BaseResource
{
    public string $routeName = 'api.core.apps';

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            /**
             * The app's display name.
             */
            'name' => $this->whenHas('name'),

            /**
             * A shorter display label for the app, used where space is limited.
             */
            'label' => $this->whenHas('label'),

            /**
             * A url-friendly identifier for the app.
             */
            'slug' => $this->whenHas('slug'),

            /**
             * A short description of the app.
             */
            'description' => $this->whenHas('description'),

            /**
             * The app's catalog category.
             */
            'category' => $this->whenHas('category'),

            /**
             * The app's unique code, used to key entitlements and frontend routing.
             */
            'code' => $this->whenHas('code'),

            /**
             * The app's brand color, as a hex string.
             */
            'color' => $this->whenHas('color'),

            /**
             * The app's icon identifier.
             */
            'icon' => $this->whenHas('icon'),

            /**
             * Whether the app is currently offered to companies.
             */
            'is_active' => $this->whenHas('is_active'),

            /**
             * When the app was released on the platform.
             */
            'released_at' => $this->whenHas('released_at'),

            /**
             * The app's permission catalog. Only present when eager-loaded, e.g. via
             * `?include=[{"name":"permissions"}]`.
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            $this->merge(parent::toArray($request)),
        ];
    }
}
