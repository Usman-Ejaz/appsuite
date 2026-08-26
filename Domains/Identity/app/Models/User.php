<?php

namespace Domains\Identity\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Domains\Core\Models\App;
use Domains\Core\Traits\HasCompany;
use Domains\Core\Traits\HasEditor;
use Domains\Identity\Contracts\Actor;
use Domains\Identity\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements Actor, PasskeyUser
{
    use HasApiTokens, HasCompany, HasEditor, HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'whatsapp', 'last_app', 'is_root', 'is_owner', 'last_activity_at', 'company_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_root' => 'boolean',
            'is_owner' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function getCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function isRoot(): bool
    {
        return $this->is_root;
    }

    public function isOwner(): bool
    {
        return $this->is_owner;
    }

    public function actorLabel(): string
    {
        return class_basename(self::class);
    }

    public function can(...$permissions): bool
    {
        return $this->hasAnyPermission($permissions);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The apps this user has been individually granted, a subset of their company's apps.
     */
    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'user_apps');
    }

    public function hasApp(string $code): bool
    {
        return $this->apps->contains('code', $code);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
