<?php

namespace Domains\Core\Traits;

use Domains\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait HasEditor
{
    protected static function bootHasEditor()
    {
        static::creating(function (BaseModel|Model $model) {
            if (Auth::check() && Schema::hasColumn($model->getTable(), 'creator_id')) {
                $model->creator_id ??= Auth::id();
                $model->creator_type = class_basename(Auth::user());
            }
        });

        static::updating(function (BaseModel|Model $model) {
            if (Auth::check() && Schema::hasColumn($model->getTable(), 'updater_id')) {
                $model->updater_id = Auth::id();
                $model->updater_type = class_basename(Auth::user());
            }
        });
    }

    /**
     * Define the polymorphic relationship for the creator.
     */
    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Define the polymorphic relationship for the updater.
     */
    public function updater(): MorphTo
    {
        return $this->morphTo();
    }
}
