<?php

namespace Domains\Identity\Models;

use Domains\Core\Models\BaseModel;
use Domains\Identity\Contracts\Actor;

class ApiKey extends BaseModel implements Actor
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        //
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 
        ];
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

    public function can(...$permissions): bool
    {
        return false;
    }
}
