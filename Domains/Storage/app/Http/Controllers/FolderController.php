<?php

namespace Domains\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Core\Http\Requests\GetResourceRequest;
use Domains\Storage\Http\Requests\Folder\CreateRequest;
use Domains\Storage\Http\Requests\Folder\UpdateRequest;
use Domains\Storage\Http\Resources\FolderCollection;
use Domains\Storage\Http\Resources\FolderResource;
use Domains\Storage\Repositories\FolderRepository;
use Illuminate\Http\JsonResponse;

/**
 * Folders organize a company's media library into a navigable tree; media
 * items are filed under a folder via their own `folder_id`.
 */
#[Group('Storage')]
class FolderController extends Controller
{
    public function __construct(protected FolderRepository $folders)
    {
        //
    }

    /**
     * Get Folders
     *
     * Returns a paginated list of folders for the authenticated company.
     */
    public function list(GetCollectionRequest $request): FolderCollection
    {
        $filters = $request->filters();

        $records = $this->folders->list($filters);

        return new FolderCollection($records);
    }

    /**
     * Create Folder
     *
     * Creates a new folder, optionally nested under a parent folder.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        $folder = $this->folders->create($input);

        return response()->json(['data' => FolderResource::make($folder)], 201);
    }

    /**
     * Get Folder
     *
     * Returns the details of a single folder.
     */
    public function get(GetResourceRequest $request, int $id): FolderResource
    {
        $filters = $request->filters();

        $record = $this->folders->get($id, $filters);

        return FolderResource::make($record);
    }

    /**
     * Update Folder
     *
     * Renames a folder or moves it under a different parent.
     */
    public function update(UpdateRequest $request, int $id): FolderResource
    {
        $input = $request->validated();

        $record = $this->folders->update($id, $input);

        return FolderResource::make($record);
    }

    /**
     * Delete Folder
     *
     * Removes a folder. Its child folders and media items are not deleted, only
     * unfiled from it.
     */
    public function delete(int $id): JsonResponse
    {
        $this->folders->delete($id);

        return response()->json(null, 204);
    }
}
