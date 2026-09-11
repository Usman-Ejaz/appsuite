<?php

namespace Domains\Shared\Traits;

use Domains\Shared\Models\Site;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSites
{
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
