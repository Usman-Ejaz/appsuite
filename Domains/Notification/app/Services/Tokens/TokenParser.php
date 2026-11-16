<?php

namespace Domains\Notification\Services\Tokens;

use BackedEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

/**
 * Replaces `{{ prefix.key }}` and `{{ prefix.key | filter:arg }}` placeholders, asking the token
 * resolver registered for each prefix, e.g. `{{ order.total | money }}`.
 *
 * An unknown or hidden token renders empty, and an unknown filter leaves the value unchanged.
 */
class TokenParser
{
    /**
     * @param  array<string, AbstractTokenResolver>  $resolvers  Keyed by prefix.
     * @param  bool  $html  Whether the template is HTML, so values must be escaped.
     */
    public function parse(?string $template, array $resolvers, bool $html = false): string
    {
        if ($template === null || $template === '') {
            return '';
        }

        return preg_replace_callback('/\{\{\s*([A-Za-z0-9_.]+)\s*((?:\|[^}]*)?)\}\}/', function (array $match) use ($resolvers, $html) {
            $value = $this->value($match[1], $resolvers);

            foreach (array_filter(explode('|', $match[2]), fn (string $filter) => trim($filter) !== '') as $filter) {
                [$name, $argument] = array_pad(explode(':', trim($filter), 2), 2, null);

                $value = $this->applyFilter(trim($name), $value, $this->unquote($argument), $match[1], $resolvers);
            }

            $value = $this->stringify($value);

            return $html ? e($value) : $value;
        }, $template) ?? $template;
    }

    /**
     * The raw value of one token, e.g. `order.total`.
     *
     * @param  array<string, AbstractTokenResolver>  $resolvers
     */
    public function value(string $token, array $resolvers): mixed
    {
        [$prefix, $path] = array_pad(explode('.', $token, 2), 2, null);

        return $path !== null && isset($resolvers[$prefix]) ? $resolvers[$prefix]->get($path) : null;
    }

    /**
     * Key a list of resolvers by their prefix.
     *
     * @param  list<AbstractTokenResolver>  $resolvers
     * @return array<string, AbstractTokenResolver>
     */
    public static function keyed(array $resolvers): array
    {
        return collect($resolvers)->keyBy(fn (AbstractTokenResolver $resolver) => $resolver->prefix())->all();
    }

    /**
     * @param  array<string, AbstractTokenResolver>  $resolvers
     */
    protected function applyFilter(string $name, mixed $value, ?string $argument, string $token, array $resolvers): mixed
    {
        return match ($name) {
            'upper' => Str::upper($this->stringify($value)),
            'lower' => Str::lower($this->stringify($value)),
            'title' => Str::title($this->stringify($value)),
            'default' => blank($value) ? $argument : $value,
            'money' => is_numeric($value) ? Number::currency((float) $value, in: $argument ?? $this->currency($token, $resolvers)) : $value,
            'number' => is_numeric($value) ? number_format((float) $value, (int) $argument) : $value,
            'date' => $this->formatDate($value, $argument ?? 'd M Y'),
            'join' => is_array($value) ? implode($argument ?? ', ', array_map($this->stringify(...), $value)) : $value,
            default => $value,
        };
    }

    /**
     * The currency for `money` without an argument: the same entity's `currency`, e.g.
     * `order.currency` for `order.total`.
     *
     * @param  array<string, AbstractTokenResolver>  $resolvers
     */
    protected function currency(string $token, array $resolvers): string
    {
        $currency = $this->value(Str::before($token, '.').'.currency', $resolvers);

        return is_string($currency) && $currency !== '' ? $currency : 'USD';
    }

    protected function formatDate(mixed $value, string $format): mixed
    {
        if (blank($value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format($format);
        } catch (Throwable) {
            return $value;
        }
    }

    protected function stringify(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? 'Yes' : 'No',
            $value instanceof BackedEnum => (string) $value->value,
            is_array($value) => implode(', ', array_map($this->stringify(...), $value)),
            is_scalar($value), $value instanceof \Stringable => (string) $value,
            default => '',
        };
    }

    protected function unquote(?string $argument): ?string
    {
        return $argument === null ? null : trim(trim($argument), '\'"');
    }
}
