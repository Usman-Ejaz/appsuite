<?php

namespace Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Auth\Actions\AuthenticateUser;
use Domains\Auth\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(protected AuthenticateUser $authenticateUser)
    {
        //
    }

    /**
     * Log In
     *
     * Authenticates a user with an email and password and issues a bearer token for use on
     * subsequent requests. The response includes the `token` string, the `token_type`
     * (always `Bearer`), the granted `scopes`, and an `expires_at` timestamp (`null` when
     * the token does not expire).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticateUser->handle(
            $request->validated('email'),
            $request->validated('password'),
        );

        return response()->json($result, 201);
    }

    /**
     * Log Out
     *
     * Revokes the token used to authenticate the current request, signing the user out of
     * the calling client. Subsequent requests made with the same token are rejected.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
