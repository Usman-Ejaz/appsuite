<?php

namespace Domains\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Auth';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auth';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->configureRateLimiting();
    }

    /**
     * `POST /api/v1/token` exchanges an API key + secret for a bearer token, so it gets the
     * same brute-force protection as the email/password `login` limiter: 5 attempts a minute
     * per key and IP.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('token', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('api_key').'|'.$request->ip());
        });
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
