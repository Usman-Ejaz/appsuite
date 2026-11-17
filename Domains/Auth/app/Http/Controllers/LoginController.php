<?php

namespace Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Auth\Actions\AuthenticateUser;
use Domains\Auth\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

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
     * Signs the caller out of the current client. For a bearer token, the token is revoked and
     * later requests with it are rejected. For a session (cookie) login from the first-party
     * SPA, the session is ended and its CSRF token rotated.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Session-authenticated requests carry a TransientToken, which has nothing to revoke.
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        } else {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(null, 204);
    }
}
