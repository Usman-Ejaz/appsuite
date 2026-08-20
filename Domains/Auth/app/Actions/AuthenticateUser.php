<?php

namespace Domains\Auth\Actions;

use Domains\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    /**
     * Verify a user's credentials and issue a full-access Sanctum bearer
     * token. Deliberately bypasses Auth::attempt()/Auth::login() — this is
     * a stateless API login, no session is created.
     *
     * @return array{token: string, token_type: string, scopes: array, expires_at: null}
     */
    public function handle(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $token = $user->createToken('login');

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'scopes' => ['*'],
            'expires_at' => null,
        ];
    }
}
