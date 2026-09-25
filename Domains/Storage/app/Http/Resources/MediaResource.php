<?php

namespace Domains\Storage\Http\Resources;

use Domains\Core\Http\Resources\BaseResource;
use Domains\Storage\Models\Media;
use Illuminate\Http\Request;

/**
 * @mixin Media
 */
class MediaResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $this->routeName = 'api.media';

        return [
            'id' => $this->id,

            'company_id' => $this->whenHas('company_id'),

            /**
             * The type of resource this media item is attached to.
             *
             * @example product
             */
            'resource_type' => $this->whenHas('resource_type'),

            /**
             * The id of the resource this media item is attached to.
             */
            'resource_id' => $this->whenHas('resource_id'),

            /**
             * The id of the library folder this media item is filed under, if any.
             */
            'folder_id' => $this->whenHas('folder_id'),

            /**
             * The folder this media item is filed under, if loaded.
             */
            'folder' => FolderResource::make($this->whenLoaded('folder')),

            /**
             * An optional title for the media item.
             */
            'title' => $this->whenHas('title'),

            /**
             * Whether the media item has been starred.
             */
            'starred' => $this->whenHas('starred'),

            /**
             * The classification of this media item.
             *
             * @example Gallery
             */
            'category' => $this->whenHas('category', fn () => $this->category?->value),

            /**
             * The filesystem disk the file is stored on.
             *
             * @example public
             */
            'disk' => $this->whenHas('disk'),

            /**
             * The original file name at the time it was uploaded.
             *
             * @example logo.png
             */
            'file_name' => $this->whenHas('file_name'),

            /**
             * The file's MIME type.
             *
             * @example image/png
             */
            'mime_type' => $this->whenHas('mime_type'),

            /**
             * The file size in bytes.
             *
             * @example 204800
             */
            'size' => $this->whenHas('size'),

            /**
             * The publicly accessible URL for this media item.
             */
            'url' => $this->whenHas('path', fn () => $this->url),

            $this->merge(parent::toArray($request)),
        ];
    }
}
