<?php

namespace Domains\Auth\Actions;

use Domains\Identity\Models\ApiKey;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AuthenticateApiKey
{
    /**
     * Exchange an API key + secret pair for a Sanctum bearer token that
     * authenticates as the API key itself — not its creator — scoped to
     * the same abilities and expiry as the key. The key is looked up
     * directly (it's a public identifier, not secret); the secret is only
     * ever compared via its hash, and hash_equals() keeps that comparison
     * constant-time.
     *
     * @return array{token: string, token_type: string, scopes: ?array, expires_at: ?Carbon}
     */
    public function handle(string $apiKey, string $apiSecret): array
    {
        $record = ApiKey::where('api_key', $apiKey)->first();

        if (! $record || ! $record->verifySecret($apiSecret) || ! $record->isActive()) {
            throw ValidationException::withMessages([
                'api_key' => ['The provided API key or secret is invalid, expired, or revoked.'],
            ]);
        }

        $record->forceFill(['last_used_at' => now()])->save();

        $token = $record->createToken($record->name, $record->abilities ?? ['*'], $record->expires_at);

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'scopes' => $record->abilities,
            'expires_at' => $record->expires_at,
        ];
    }
}
