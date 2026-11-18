<?php

namespace Domains\Notification\Services\Tokens;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

/**
 * Replaces `{{ prefix.key }}` and `{{ prefix.key | filter:arg }}` placeholders, asking the token
 * resolver registered for each prefix, e.g. `{{ order.total | money }}`.
 *
 * An unknown or hidden token renders empty, and an unknown filter leaves the value unchanged.
 * Values are escaped in HTML templates; only the `table` filter's own markup is not.
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

                $value = $this->applyFilter(trim($name), $value, $this->unquote($argument), $match[1], $resolvers, $html);
            }

            return match (true) {
                $html && $value instanceof Htmlable => $value->toHtml(),
                $html => e($this->stringify($value)),
                default => $this->stringify($value),
            };
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
     * Text: `upper`, `lower`, `title`, `capitalize`, `trim`, `truncate:100`, `words:20`, `default:'—'`.
     * Numbers: `money:'USD'`, `number:2`, `percent:1`, `abbreviate:1`.
     * Dates: `date:'d M Y'`, `datetime:'d M Y H:i'`, `time:'H:i'`, `ago`.
     * Lists: `join:', '`, `count`, `first`, `last`, `table:'column,column'`. Booleans: `yesno:'Yes,No'`.
     *
     * @param  array<string, AbstractTokenResolver>  $resolvers
     * @param  bool  $html  Whether the template is HTML, for filters that render markup.
     */
    protected function applyFilter(string $name, mixed $value, ?string $argument, string $token, array $resolvers, bool $html = false): mixed
    {
        return match ($name) {
            'upper' => Str::upper($this->stringify($value)),
            'lower' => Str::lower($this->stringify($value)),
            'title' => Str::title($this->stringify($value)),
            'capitalize' => Str::ucfirst($this->stringify($value)),
            'trim' => trim($this->stringify($value)),
            'truncate' => Str::limit($this->stringify($value), (int) ($argument ?? 100)),
            'words' => Str::words($this->stringify($value), (int) ($argument ?? 20)),
            'default' => blank($value) ? $argument : $value,
            'money' => is_numeric($value) ? Number::currency((float) $value, in: $argument ?? $this->currency($token, $resolvers)) : $value,
            'number' => is_numeric($value) ? number_format((float) $value, (int) $argument) : $value,
            'percent' => is_numeric($value) ? Number::percentage((float) $value, (int) $argument, $argument === null ? 2 : null) : $value,
            'abbreviate' => is_numeric($value) ? Number::abbreviate((float) $value, maxPrecision: (int) ($argument ?? 1)) : $value,
            'date' => $this->toDate($value)?->format($argument ?? 'd M Y') ?? $value,
            'datetime' => $this->toDate($value)?->format($argument ?? 'd M Y H:i') ?? $value,
            'time' => $this->toDate($value)?->format($argument ?? 'H:i') ?? $value,
            'ago' => $this->toDate($value)?->diffForHumans() ?? $value,
            'join' => is_array($value) ? implode($argument ?? ', ', array_map($this->stringify(...), $value)) : $value,
            'count' => is_countable($value) ? count($value) : $value,
            'first' => is_array($value) ? Arr::first($value) : $value,
            'last' => is_array($value) ? Arr::last($value) : $value,
            'table' => $this->table($value, $argument, $html),
            'yesno' => $this->yesNo($value, $argument ?? 'Yes,No'),
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

    /**
     * The value as a date, or null when it is blank or not a date.
     */
    protected function toDate(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Label a boolean-like value (`true`, `1`, `"0"`, `"yes"`…) with the `yes,no` labels; any
     * other value is left unchanged.
     */
    protected function yesNo(mixed $value, string $labels): mixed
    {
        $boolean = $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($boolean === null) {
            return $value;
        }

        [$yes, $no] = array_pad(explode(',', $labels, 2), 2, '');

        return trim($boolean ? $yes : $no);
    }

    /**
     * A list of rows (e.g. `order.items`), a key/value array (e.g. `submission.data`) or a plain
     * list as a table: HTML in HTML templates, one line per row otherwise. The optional
     * `table:'product,total'` argument picks the columns, or the keys of a key/value array.
     */
    protected function table(mixed $value, ?string $columns, bool $html): mixed
    {
        if (! is_array($value) || $value === []) {
            return $value;
        }

        $keys = $columns === null ? null : array_map(trim(...), explode(',', $columns));

        if (! array_is_list($value)) {
            $rows = collect($keys ?? array_keys($value))
                ->filter(fn (int|string $key) => array_key_exists($key, $value))
                ->map(fn (int|string $key) => [Str::headline((string) $key), $this->stringify($value[$key])])
                ->values()
                ->all();

            return $this->renderTable(null, $rows, ': ', $html);
        }

        if (! collect($value)->every(fn (mixed $row) => is_array($row))) {
            return $this->renderTable(null, array_map(fn (mixed $item) => [$this->stringify($item)], $value), '', $html);
        }

        $keys ??= collect($value)->flatMap(fn (array $row) => array_keys($row))->unique()->values()->all();

        return $this->renderTable(
            array_map(fn (int|string $key) => Str::headline((string) $key), $keys),
            array_map(fn (array $row) => array_map(fn (int|string $key) => $this->stringify($row[$key] ?? null), $keys), $value),
            ' | ',
            $html,
        );
    }

    /**
     * Every cell is escaped, so only the table markup itself is trusted.
     *
     * @param  list<string>|null  $headers
     * @param  list<list<string>>  $rows
     */
    protected function renderTable(?array $headers, array $rows, string $separator, bool $html): string|HtmlString
    {
        if (! $html) {
            return collect($headers === null ? $rows : [$headers, ...$rows])
                ->map(fn (array $cells) => implode($separator, $cells))
                ->implode("\n");
        }

        $cell = fn (string $tag, string $text) => "<{$tag} style=\"text-align:left;padding:6px 8px;border-bottom:1px solid #e5e7eb\">".e($text)."</{$tag}>";
        $row = fn (string $tag, array $cells) => '<tr>'.implode('', array_map(fn (string $text) => $cell($tag, $text), $cells)).'</tr>';

        return new HtmlString(
            '<table style="width:100%;border-collapse:collapse">'
            .($headers === null ? '' : '<thead>'.$row('th', $headers).'</thead>')
            .'<tbody>'.implode('', array_map(fn (array $cells) => $row('td', $cells), $rows)).'</tbody>'
            .'</table>'
        );
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
