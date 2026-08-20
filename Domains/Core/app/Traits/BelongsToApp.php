<?php

namespace Domains\Core\Traits;

use Domains\Core\Models\App;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToApp
{
    /**
     * Define the polymorphic relationship for the creator.
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
