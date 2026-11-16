<?php

namespace Domains\Shared\Traits;

use Domains\Shared\Models\Site;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSite
{
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
