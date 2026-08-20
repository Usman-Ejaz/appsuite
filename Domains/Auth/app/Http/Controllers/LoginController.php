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
     * Authenticate a user and issue a bearer token for subsequent requests.
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
     * Revoke the current access token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
