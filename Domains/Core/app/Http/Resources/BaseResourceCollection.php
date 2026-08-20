<?php

namespace Domains\Core\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @property int $id
 *
 * @mixin Model
 */
class BaseResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Successful Response',
            /** @var MetaResource */
            'meta' => [],
            /** @var LinksResource */
            'links' => [],
        ];
    }
}
