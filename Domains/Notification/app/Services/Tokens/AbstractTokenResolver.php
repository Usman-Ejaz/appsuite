<?php

namespace Domains\Notification\Services\Tokens;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Resolves the `{{ prefix.key }}` tokens of one entity, e.g. `{{ order.total }}`.
 *
 * Tokens are dynamic: every visible attribute of the model is a token, so a new column is usable
 * in templates without touching code. A subclass only adds computed tokens, as `get{Key}()`
 * methods (e.g. `getPreviousStatus()` for `{{ order.previous_status }}`), and narrows what is
 * exposed with `$only` or `$except`. The model's own `$hidden` attributes are never exposed.
 */
abstract class AbstractTokenResolver
{
    /**
     * When set, the only attributes exposed as tokens.
     *
     * @var list<string>
     */
    protected array $only = [];

    /**
     * Attributes never exposed as tokens, on top of the model's hidden ones.
     *
     * @var list<string>
     */
    protected array $except = ['company_id', 'creator_type', 'creator_id', 'updater_type', 'updater_id'];

    /**
     * The model's visible attributes, read once.
     *
     * @var array<string, mixed>|null
     */
    private ?array $attributes = null;

    public function __construct(protected ?Model $model = null) {}

    /**
     * The token prefix, e.g. `order` for `{{ order.total }}`.
     */
    abstract public function prefix(): string;

    /**
     * The value of a token below this prefix, e.g. `total` or `data.email`.
     */
    public function get(string $path): mixed
    {
        [$key, $rest] = array_pad(explode('.', $path, 2), 2, null);

        $value = $this->computed($key) ?? $this->attribute($key);

        return $rest === null ? $value : (is_array($value) ? data_get($value, $rest) : null);
    }

    /**
     * Every token this resolver offers, for the token picker: the model's columns and appended
     * attributes, minus hidden and excluded ones, plus the computed tokens.
     *
     * @return array<int, string>
     */
    public function tokens(): array
    {
        $attributes = $this->model
            ? [...Schema::getColumnListing($this->model->getTable()), ...$this->model->getAppends()]
            : [];

        return collect($attributes)
            ->filter(fn (string $key) => $this->exposes($key))
            ->merge($this->computedKeys())
            ->unique()
            ->map(fn (string $key) => $this->prefix().'.'.$key)
            ->values()
            ->all();
    }

    protected function computed(string $key): mixed
    {
        $method = 'get'.Str::studly($key);

        return in_array($key, $this->computedKeys(), true) ? $this->{$method}() : null;
    }

    protected function attribute(string $key): mixed
    {
        if (! $this->model || ! $this->exposes($key)) {
            return null;
        }

        $this->attributes ??= $this->model->attributesToArray();

        return $this->attributes[$key] ?? null;
    }

    protected function exposes(string $key): bool
    {
        if ($this->only !== []) {
            return in_array($key, $this->only, true);
        }

        return ! in_array($key, [...$this->except, ...($this->model?->getHidden() ?? [])], true);
    }

    /**
     * The keys of the `get{Key}()` methods a subclass declares.
     *
     * @return array<int, string>
     */
    protected function computedKeys(): array
    {
        return collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn (ReflectionMethod $method) => $method->getDeclaringClass()->getName() !== self::class
                && str_starts_with($method->getName(), 'get')
                && $method->getName() !== 'get'
                && $method->getNumberOfParameters() === 0)
            ->map(fn (ReflectionMethod $method) => Str::snake(substr($method->getName(), 3)))
            ->values()
            ->all();
    }
}
