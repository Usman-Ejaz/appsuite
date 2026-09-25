<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\App;
use Domains\Core\Models\BaseModel;
use Domains\Identity\Database\Factories\CompanyFactory;
use Domains\Identity\Enums\CompanyStatus;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Traits\HasMedia;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Company extends BaseModel
{
    use HasMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'slug', 'description', 'status', 'address', 'license_number',
    ];

    /**
     * The attributes that are able to cast.
     */
    protected function casts(): array
    {
        return [
            'status' => CompanyStatus::class,
            'joined_at' => 'datetime',
        ];
    }

    /**
     * Stamps joined_at at creation time if it wasn't already set — when this
     * tenant joined the platform, not something an API caller backdates.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (Company $company) {
            $company->joined_at ??= now();
        });
    }

    /**
     * The roles that belong to this company.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * The API keys that belong to this company.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * The apps this company has subscribed to.
     */
    public function apps(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'company_apps');
    }

    public function hasApp(string $code): bool
    {
        return $this->apps->contains('code', $code);
    }

    /**
     * The company's branding logo.
     */
    public function logo(): MorphOne
    {
        return $this->singleMediaOfCategory(MediaCategory::LOGO);
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
