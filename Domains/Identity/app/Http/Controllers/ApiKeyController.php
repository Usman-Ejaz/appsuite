<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Core\Http\Requests\GetCollectionRequest;
use Domains\Identity\Actions\IssueApiKey;
use Domains\Identity\Http\Requests\ApiKey\CreateRequest;
use Domains\Identity\Http\Resources\ApiKeyResource;
use Domains\Identity\Repositories\ApiKeyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Identity')]
class ApiKeyController extends Controller
{
    public function __construct(
        protected ApiKeyRepository $apiKeys,
        protected IssueApiKey $issueApiKey,
    ) {
        //
    }

    /**
     * Get Api Keys
     *
     * Returns the API keys belonging to the current user's company.
     */
    public function list(GetCollectionRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $records = $this->apiKeys
            ->list($filters);

        return ApiKeyResource::collection($records);
    }

    /**
     * Create Api Key
     *
     * Issues a new API key for the current user's company. The `api_secret` is only ever shown
     * here, at creation time. It is stored hashed and cannot be retrieved again afterward.
     */
    public function create(CreateRequest $request): JsonResponse
    {
        $input = $request->validated();

        ['apiKey' => $apiKey, 'plainSecret' => $plainSecret] = $this->issueApiKey->handle(
            $request->user()->company,
            $input,
        );

        return response()->json([
            'data' => ApiKeyResource::make($apiKey),
            'api_secret' => $plainSecret,
        ], 201);
    }

    /**
     * Delete Api Key
     *
     * Revokes an API key belonging to the current user's company.
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $this->apiKeys
            ->delete($id);

        return response()->json(null, 204);
    }
}
