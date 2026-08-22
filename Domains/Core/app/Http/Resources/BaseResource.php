<?php

namespace Domains\Core\Http\Resources;

use Carbon\Carbon;
use Domains\Identity\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $creator_id
 * @property int $updater_id
 *
 * @mixin Model
 */
class BaseResource extends JsonResource
{
    public string $routeName;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fields = [
            'id' => $this->id,

            /** @var Carbon */
            'created_at' => $this->whenHas('created_at'),
            /** @var Carbon */
            'updated_at' => $this->whenHas('updated_at'),

            /** @var int */
            'creator_id' => $this->whenHas('creator_id'),
            /** @var string */
            'creator_type' => $this->whenHas('creator_type'),
            'creator' => UserResource::make($this->whenLoaded('creator')),

            /** @var int */
            'updater_id' => $this->whenHas('updater_id'),
            /** @var string */
            'updater_type' => $this->whenHas('updater_type'),
            'updater' => UserResource::make($this->whenLoaded('updater')),

            '_links' => $this->getLinks(),
        ];

        return $fields;
    }

    public static function delete($data = null, $message = 'Record has been deleted successfully')
    {
        return ['data' => $data, 'message' => $message];
    }

    public static function any($data = null, $status = true, $meta = null, $message = null)
    {
        return [
            'status' => $status,
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
        ];
    }

    public function getLinks(): ?array
    {

        if (! empty($this->routeName) && ! empty($this->id)) {
            $selfUrl = Route::has("$this->routeName.get") ? route("{$this->routeName}.get", ['id' => $this->id]) : null;
            $parentUrl = Route::has("$this->routeName.list") ? route("{$this->routeName}.list") : null;

            $links = [
                'self' => $this->whenNotNull($selfUrl),
                'parent' => $this->whenNotNull($parentUrl),
            ];

            return $links;
        }

        return null;
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
            'meta' => null,
            'links' => null,
        ];
    }
}
