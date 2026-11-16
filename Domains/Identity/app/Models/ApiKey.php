<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\BaseModel;
use Domains\Identity\Contracts\Actor;
use Domains\Identity\Database\Factories\ApiKeyFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * Implements AuthorizableContract (via the existing `can()`) so it can be passed to the Gate:
 * spatie/laravel-permission registers a `Gate::before` hook typed to that contract, which
 * otherwise throws a TypeError for every gate check made with an API key.
 */
class ApiKey extends BaseModel implements Actor, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id', 'name', 'abilities', 'expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = ['api_secret'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function isRoot(): bool
    {
        return false;
    }

    public function isOwner(): bool
    {
        return false;
    }

    public function actorLabel(): string
    {
        return class_basename(self::class);
    }

    /**
     * Accepts permissions variadically or as one array — the `permission` gate passes an
     * array — so they're flattened before checking. Every one must be granted; `*` grants all.
     * Null or empty abilities grant nothing (full access is stored explicitly as `['*']`).
     */
    public function can(...$permissions): bool
    {
        if (empty($this->abilities)) {
            return false;
        }

        return collect($permissions)->flatten()->every(
            fn ($permission) => in_array($permission, $this->abilities, true) || in_array('*', $this->abilities, true)
        );
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    public function verifySecret(string $secret): bool
    {
        return hash_equals($this->api_secret, hash('sha256', $secret));
    }

    /**
     * @return array{key: string, secret: string}
     */
    public static function generateCredentials(): array
    {
        return [
            'key' => Str::random(32),
            'secret' => Str::random(40),
        ];
    }

    protected static function newFactory(): ApiKeyFactory
    {
        return ApiKeyFactory::new();
    }
}
