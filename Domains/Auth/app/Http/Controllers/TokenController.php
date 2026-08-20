<?php

namespace Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Auth\Actions\AuthenticateApiKey;
use Domains\Auth\Http\Requests\TokenRequest;
use Illuminate\Http\JsonResponse;

class TokenController extends Controller
{
    public function __construct(protected AuthenticateApiKey $authenticateApiKey)
    {
        //
    }

    /**
     * Exchange an API key + secret pair for a Sanctum bearer token, scoped
     * to the same abilities and expiry as the API key. Intended for
     * server-to-server integrations that authenticate as the API key
     * itself rather than as a specific user.
     */
    public function create(TokenRequest $request): JsonResponse
    {
        $result = $this->authenticateApiKey->handle(
            $request->validated('api_key'),
            $request->validated('api_secret'),
        );

        return response()->json($result, 201);
    }
}
