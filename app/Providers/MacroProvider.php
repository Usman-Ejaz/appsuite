<?php

namespace App\Providers;

use Domains\Core\Models\App;
use Domains\Identity\Models\Company;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class MacroProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerRequestMacros();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerSchemaMacros();
    }

    protected function registerSchemaMacros()
    {
        Blueprint::macro('company', function () {
            /** @var Blueprint $this */
            return $this->foreignIdFor(Company::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        });

        Blueprint::macro('dropCompany', function () {
            /** @var Blueprint $this */
            $this->dropForeignIdFor(Company::class);
        });

        Blueprint::macro('app', function () {
            /** @var Blueprint $this */
            return $this->foreignIdFor(App::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        });

        Blueprint::macro('dropApp', function () {
            /** @var Blueprint $this */
            $this->dropForeignIdFor(App::class);
        });

        Blueprint::macro('creator', function () {
            /** @var Blueprint $this */
            return $this->nullableMorphs('creator');
        });

        Blueprint::macro('dropCreator', function () {
            /** @var Blueprint $this */
            $this->dropMorphs('creator');
        });

        Blueprint::macro('updater', function () {
            /** @var Blueprint $this */
            return $this->nullableMorphs('updater');
        });

        Blueprint::macro('dropUpdater', function () {
            /** @var Blueprint $this */
            $this->dropMorphs('updater');
        });

        Blueprint::macro('editor', function () {
            /** @var Blueprint $this */
            $this->creator();

            /** @var Blueprint $this */
            return $this->updater();
        });

        Blueprint::macro('dropEditor', function () {
            /** @var Blueprint $this */
            $this->dropUpdater();

            /** @var Blueprint $this */
            $this->dropCreator();
        });
    }

    protected function registerRequestMacros()
    {
        Request::macro('filter', function () {
            /** @var Request $this */
            return $this->query('__f', []);
        });

        Request::macro('filters', fn () => $this->filter());
    }
}
