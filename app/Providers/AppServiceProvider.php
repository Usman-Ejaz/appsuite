<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Domains\Core\Models\App;
use Domains\Identity\Contracts\Actor;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use function Illuminate\Support\enum_value;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        $this->registerGates();

        $this->registerMorphMap();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerGates()
    {
        Gate::before(function (Actor $actor) {
            if ($actor->isRoot()) {
                return true;
            }
        });

        Gate::define('permission', function (Actor $actor, \BackedEnum|array|string $permission) {
            if ($actor->isOwner()) {
                return true;
            }

            $permissions = array_map(
                fn (\BackedEnum|string $permission): string => enum_value($permission),
                Arr::wrap($permission),
            );

            return $actor->can($permissions);
        });
    }

    /**
     * Short, stable aliases for every model that can have media attached, so a
     * polymorphic `resource_type` round-trips as e.g. "product" rather than its full
     * class name. Only registers the alias translation for these models — it does not
     * affect any other polymorphic relation in the app.
     */
    protected function registerMorphMap(): void
    {
        Relation::morphMap([
            'Product' => Product::class,
            'Company' => Company::class,
            'App' => App::class,
            'User' => User::class,
        ]);
    }
}
