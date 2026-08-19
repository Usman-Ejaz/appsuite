<?php

namespace Domains\Core\Traits;

use Domains\Core\Models\BaseModel;
use Domains\Core\Models\Scopes\CompanyScope;
use Domains\Identity\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait HasCompany
{
    protected static function bootHasCompany()
    {
        static::creating(function (BaseModel|Model $model) {
            if (Auth::check() && Schema::hasColumn($model->getTable(), 'company_id')) {
                $model->company_id = Auth::user()?->company_id;
            }
        });

        static::updating(function (BaseModel|Model $model) {
            if (Auth::check() && Schema::hasColumn($model->getTable(), 'company_id') && empty($model->company_id)) {
                $model->company_id = Auth::user()?->company_id;
            }
        });

        $request = request();

        $user = $request->user();

        static::addGlobalScope(new CompanyScope($user));
    }

    /**
     * Define the polymorphic relationship for the creator.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
