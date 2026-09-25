<?php

namespace Domains\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Http\Requests\Media\CreateRequest;
use Domains\Storage\Http\Requests\Media\UpdateRequest;
use Domains\Storage\Http\Resources\MediaCollection;
use Domains\Storage\Http\Resources\MediaResource;
use Domains\Storage\Repositories\MediaRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;

/**
 * A generic upload endpoint attaching media to any resource whose model uses the
 * HasMedia trait and is registered in the app's Relation::morphMap(), rather than a
 * per-resource upload action.
 */
#[Group('Storage')]
class MediaController extends Controller
{
    public function __construct(protected MediaRepository $media)
    {
        //
    }

    /**
     * Get Media
     *
     * Returns a paginated list of media items for the authenticated company.
     */
    public function list(GetCollectionRequest $request): MediaCollection
    {
        $filters = $request->filters();

        $records = $this->media->list($filters);

        return new MediaCollection($records);
    }

    /**
     * Create Media
     *
     * Uploads one or more files, and/or downloads one or more URLs, attaching each
     * as a media item to the given resource.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $resourceClass = Relation::getMorphedModel($input['resource_type']);
        $resource = $resourceClass::findOrFail($input['resource_id']);

        $media = $this->media->upload(
            $resource,
            $request->file('files', []),
            $input['urls'] ?? [],
            [
                'disk' => $input['disk'] ?? null,
                'category' => empty($input['category']) ? null : MediaCategory::from($input['category']),
                'folder_id' => $input['folder_id'] ?? null,
                'title' => $input['title'] ?? null,
                'starred' => $input['starred'] ?? false,
            ],
        );

        return response()->json(['data' => MediaResource::collection($media)], 201);
    }

    /**
     * Get Media Item
     *
     * Returns the details of a single media item.
     */
    public function get(GetResourceRequest $request, int $id): MediaResource
    {
        $filters = $request->filters();

        $record = $this->media->get($id, $filters);

        return MediaResource::make($record);
    }

    /**
     * Update Media Item
     *
     * Updates a media item's metadata. The underlying file is never replaced this way.
     */
    public function update(UpdateRequest $request, int $id): MediaResource
    {
        $input = $request->validated();

        $record = $this->media->update($id, $input);

        return MediaResource::make($record);
    }

    /**
     * Delete Media Item
     *
     * Permanently removes a media item and its underlying file.
     */
    public function delete(int $id): JsonResponse
    {
        $this->media->delete($id);

        return response()->json(null, 204);
    }
}
