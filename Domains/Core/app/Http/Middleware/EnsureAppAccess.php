<?php

namespace Domains\Core\Http\Middleware;

use Closure;
use Domains\Identity\Models\Company;
use Domains\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppAccess
{
    private const CACHE_TTL_MINUTES = 5;

    /**
     * Ensure the acting Actor's company (and, for a regular user, the user
     * individually) is subscribed to the given app before letting the
     * request through. Cached in Redis to avoid a DB round-trip on every
     * request — app subscriptions change rarely, so a short bounded
     * staleness window is an acceptable trade.
     */
    public function handle(Request $request, Closure $next, string $code): Response
    {
        $actor = $request->user();

        abort_if(! $actor, 401);

        if ($actor->isRoot()) {
            return $next($request);
        }

        $companyId = $actor->getCompanyId();

        abort_if(! $companyId, 403);

        $companyHasApp = Cache::store('redis')->remember(
            "app-access:company:{$companyId}:{$code}",
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn () => Company::query()->find($companyId)?->hasApp($code) ?? false,
        );

        abort_unless($companyHasApp, 403);

        if ($actor instanceof User && ! $actor->isOwner()) {
            $userHasApp = Cache::store('redis')->remember(
                "app-access:user:{$actor->id}:{$code}",
                now()->addMinutes(self::CACHE_TTL_MINUTES),
                fn () => $actor->hasApp($code),
            );

            abort_unless($userHasApp, 403);
        }

        return $next($request);
    }
}
