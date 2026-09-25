<?php

namespace Domains\Storage\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Storage\Models\Folder;
use Illuminate\Http\Request;

/**
 * @mixin Folder
 */
class FolderResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $this->routeName = 'api.folders';

        return [
            'id' => $this->id,

            /**
             * The id of the parent folder, if any.
             */
            'parent_id' => $this->whenHas('parent_id'),

            /**
             * The parent folder, if loaded.
             */
            'parent' => self::make($this->whenLoaded('parent')),

            /**
             * The direct child folders, if loaded.
             */
            'children' => self::collection($this->whenLoaded('children')),

            /**
             * The folder's display name.
             */
            'name' => $this->whenHas('name'),

            $this->merge(parent::toArray($request)),
        ];
    }
}
