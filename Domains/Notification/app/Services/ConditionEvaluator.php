<?php

namespace Domains\Notification\Services;

use BackedEnum;
use Closure;
use Domains\Notification\Enums\ConditionOperator;
use Illuminate\Support\Str;

/**
 * Decides whether a trigger action's conditions pass for one firing.
 *
 * Conditions look like `{"match": "All", "rules": [{"field": "order.total", "operator": "Gte",
 * "value": 500}]}`. Fields are tokens, read through the same resolvers templates use, so any
 * token can be a condition. `ChangedTo` and `ChangedFrom` compare against the values before the
 * change, keyed by token.
 */
class ConditionEvaluator
{
    /**
     * @param  array{match?: string, rules?: list<array{field?: string, operator?: string, value?: mixed}>}|null  $conditions
     * @param  Closure(string): mixed  $value  Reads a token's current value.
     * @param  array<string, mixed>  $previous  Values before the change, keyed by token.
     */
    public function passes(?array $conditions, Closure $value, array $previous = []): bool
    {
        $rules = $conditions['rules'] ?? [];

        if ($rules === []) {
            return true;
        }

        $any = ($conditions['match'] ?? 'All') === 'Any';

        foreach ($rules as $rule) {
            $passes = $this->rulePasses($rule, $value, $previous);

            if ($any && $passes) {
                return true;
            }

            if (! $any && ! $passes) {
                return false;
            }
        }

        return ! $any;
    }

    /**
     * @param  array{field?: string, operator?: string, value?: mixed}  $rule
     * @param  Closure(string): mixed  $value
     * @param  array<string, mixed>  $previous
     */
    protected function rulePasses(array $rule, Closure $value, array $previous): bool
    {
        $operator = ConditionOperator::tryFrom($rule['operator'] ?? '');
        $field = $rule['field'] ?? null;

        if (! $operator || ! $field) {
            return false;
        }

        $actual = $this->normalize($value($field));
        $expected = $this->normalize($rule['value'] ?? null);
        $before = $this->normalize($previous[$field] ?? null);

        return match ($operator) {
            ConditionOperator::EQ => $this->equals($actual, $expected),
            ConditionOperator::NEQ => ! $this->equals($actual, $expected),
            ConditionOperator::GT => $this->numeric($actual, $expected, fn ($a, $b) => $a > $b),
            ConditionOperator::GTE => $this->numeric($actual, $expected, fn ($a, $b) => $a >= $b),
            ConditionOperator::LT => $this->numeric($actual, $expected, fn ($a, $b) => $a < $b),
            ConditionOperator::LTE => $this->numeric($actual, $expected, fn ($a, $b) => $a <= $b),
            ConditionOperator::IN => $this->in($actual, $expected),
            ConditionOperator::NOT_IN => ! $this->in($actual, $expected),
            ConditionOperator::CONTAINS => $this->contains($actual, $expected),
            ConditionOperator::STARTS_WITH => is_scalar($actual) && Str::startsWith(Str::lower((string) $actual), Str::lower((string) $expected)),
            ConditionOperator::IS_EMPTY => blank($actual),
            ConditionOperator::IS_NOT_EMPTY => filled($actual),
            ConditionOperator::CHANGED_TO => $this->equals($actual, $expected) && ! $this->equals($before, $expected),
            ConditionOperator::CHANGED_FROM => $this->equals($before, $expected) && ! $this->equals($actual, $expected),
        };
    }

    protected function normalize(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }

    protected function equals(mixed $actual, mixed $expected): bool
    {
        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        if (is_bool($actual) || is_bool($expected)) {
            return filter_var($actual, FILTER_VALIDATE_BOOL) === filter_var($expected, FILTER_VALIDATE_BOOL);
        }

        return is_scalar($actual) && is_scalar($expected)
            ? (string) $actual === (string) $expected
            : $actual === $expected;
    }

    /**
     * @param  callable(float, float): bool  $compare
     */
    protected function numeric(mixed $actual, mixed $expected, callable $compare): bool
    {
        return is_numeric($actual) && is_numeric($expected) && $compare((float) $actual, (float) $expected);
    }

    protected function in(mixed $actual, mixed $expected): bool
    {
        foreach ((array) $expected as $candidate) {
            if ($this->equals($actual, $this->normalize($candidate))) {
                return true;
            }
        }

        return false;
    }

    protected function contains(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual)) {
            return $this->in($expected, $actual);
        }

        return is_scalar($actual) && is_scalar($expected) && Str::contains(Str::lower((string) $actual), Str::lower((string) $expected));
    }
}
