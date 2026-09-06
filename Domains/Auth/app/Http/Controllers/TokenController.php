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
     * Create Token
     *
     * Exchanges an API key and secret for a bearer token that authenticates as the API key
     * itself rather than a specific user. Intended for server-to-server integrations. The
     * response includes the `token` string, the `token_type` (always `Bearer`), the
     * `scopes` granted to the key, and an `expires_at` timestamp (`null` when the token
     * does not expire).
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
