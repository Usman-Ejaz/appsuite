<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Domains\Identity\Actions\IssueApiKey;
use Domains\Identity\Actions\RevokeApiKey;
use Domains\Identity\Http\Requests\ApiKey\CreateRequest;
use Domains\Identity\Http\Resources\ApiKeyResource;
use Domains\Identity\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Identity')]
class ApiKeyController extends Controller
{
    public function __construct(
        protected IssueApiKey $issueApiKey,
        protected RevokeApiKey $revokeApiKey,
    ) {
        //
    }

    /**
     * Get Api Keys
     *
     * Returns the API keys belonging to the current user's company.
     */
    public function list(Request $request): AnonymousResourceCollection
    {
        $apiKeys = ApiKey::query()
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return ApiKeyResource::collection($apiKeys);
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
        $apiKey = ApiKey::query()
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $this->revokeApiKey->handle($apiKey);

        return response()->json(null, 204);
    }
}
