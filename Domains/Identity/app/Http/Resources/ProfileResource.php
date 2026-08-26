<?php

namespace Domains\Identity\Http\Resources;

use Domains\Core\Models\App;
use Domains\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $apps = $this->resolveApps();
        $currentApp = $this->resolveCurrentApp($apps);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->whenLoaded('company', fn () => $this->company ? [
                'name' => $this->company->name,
                'status' => $this->company->status,
                'plan' => null,
            ] : null),
            'recent_app' => $currentApp?->code,
            'permissions' => $this->getAllPermissions()
                ->filter(fn ($permission) => $permission->app_id === null || $permission->app_id === $currentApp?->id)
                ->pluck('name')
                ->values(),
            'apps' => $apps->map(fn ($app) => [
                'name' => $app->name,
                'code' => $app->code,
                'color' => $app->color,
                'icon' => $app->icon,
            ])->values(),
        ];
    }

    /**
     * Which apps this profile lists depends on the user's standing: root sees every app in the
     * system; a company owner sees everything the company is subscribed to; anyone else only
     * sees the apps they've been individually granted via user_apps.
     *
     * @return Collection<int, App>
     */
    protected function resolveApps(): Collection
    {
        if ($this->isRoot()) {
            return App::query()->system()->get();
        }

        if ($this->isOwner()) {
            return $this->company?->apps ?? collect();
        }

        return $this->apps ?? collect();
    }

    /**
     * Prefers the app the user was last in (last_app), provided it's still in their apps list —
     * falls back to the alphabetically-first app in that list otherwise (e.g. first login, or the
     * last app was since revoked).
     *
     * @param  Collection<int, App>  $apps
     */
    protected function resolveCurrentApp(Collection $apps): ?App
    {
        if ($apps->isEmpty() || $this->isRoot()) {
            return null;
        }

        return $apps->firstWhere('code', $this->last_app)
            ?? $apps->sortBy('name')->first();
    }
}
