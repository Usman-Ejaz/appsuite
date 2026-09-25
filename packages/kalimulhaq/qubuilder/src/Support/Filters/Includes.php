<?php

namespace Kalimulhaq\Qubuilder\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Kalimulhaq\Qubuilder\Qubuilder;
use Kalimulhaq\Qubuilder\Support\Helper;

/**
 * Applies eager-loading of relationships to the builder.
 *
 * Each include entry may carry its own `select`, `filter`, `sort`, `include`,
 * `page`, and `limit` keys to scope the loaded relationship. Aggregates
 * (`count`, `avg`, `sum`, `min`, `max`) are also supported. Polymorphic
 * `MorphTo` relations are handled via `morphWith()` when the model exposes a
 * `{relation}Map()` method.
 */
class Includes
{
    /**
     * The list of include definitions.
     *
     * @var array<int, array<string, mixed>>
     */
    private $includes = [];

    /**
     * @param  array<int, array<string, mixed>>|null  $includes  Array of include definition arrays.
     */
    public function __construct(?array $includes = [])
    {
        $this->includes = ! empty($includes) ? $includes : [];
    }

    /**
     * Apply all includes to the builder.
     *
     * @template TFilterModel of Model
     *
     * @param  Builder<TFilterModel>  $builder
     * @return Builder<TFilterModel>
     */
    public function build(Builder $builder): Builder
    {
        foreach ($this->includes as $include) {
            $name = $include['name'] ?? '';
            $model = $builder->getModel();

            if (! empty($name)) {
                $commonFilters = Arr::only($include, ['select', 'filter', 'include', 'sort', 'group', 'page', 'limit']);
                $aggregateFilter = $include['filter'] ?? null;
                $aggregate = $include['aggregate'] ?? null;
                $field = $include['field'] ?? null;

                // Scope the aggregate sub-query by its `filter` only — applying the full
                // pipeline (select/sort/group) would clobber the aggregate's own SELECT.
                /** @param  Builder<Model>  $subBuilder */
                $scopeAggregate = fn (Builder $subBuilder) => (new Where($aggregateFilter))->build($subBuilder);

                switch ($aggregate) {
                    case 'count':
                        $builder->withCount([$name => $scopeAggregate]);
                        break;
                    case 'avg':
                        $builder->withAvg([$name => $scopeAggregate], $field);
                        break;
                    case 'sum':
                        $builder->withSum([$name => $scopeAggregate], $field);
                        break;
                    case 'min':
                        $builder->withMin([$name => $scopeAggregate], $field);
                        break;
                    case 'max':
                        $builder->withMax([$name => $scopeAggregate], $field);
                        break;
                    default:
                        if (Helper::getReturnTypes(get_class($model), $name) === MorphTo::class && method_exists($model, $name.'Map')) {
                            $builder->with([$name => function ($morphBuilder) use ($commonFilters, $model, $name) {
                                $morphWith = [];
                                $morphMaping = $model->{$name.'Map'}();

                                foreach ($morphMaping as $morpto => $relations) {
                                    // morphWith() resolves morphable eager-loads by class name,
                                    // so map a morph alias (e.g. 'post') to its FQCN when one is
                                    // registered; otherwise the key is assumed to be a class name.
                                    $morphClass = Relation::getMorphedModel($morpto) ?? $morpto;

                                    foreach (Helper::include($commonFilters) as $inputInclude) {
                                        if (in_array($inputInclude['name'], $relations)) {
                                            Arr::set(
                                                $morphWith, $morphClass.'.'.$inputInclude['name'],
                                                /** @param  Relation<Model, Model, mixed>  $subBuilder */
                                                fn (Relation $subBuilder) => Qubuilder::make($inputInclude, $subBuilder)->query()
                                            );
                                        }
                                    }
                                }

                                $morphBuilder->morphWith($morphWith);
                            }]);
                        } else {
                            if (! empty($commonFilters)) {
                                $builder->with([$name => function (Relation $subBuilder) use ($commonFilters) {
                                    $built = Qubuilder::make($commonFilters, $subBuilder)->query();

                                    return $this->applyRelationLimit($subBuilder, $built, $commonFilters);
                                }]);
                            } else {
                                $builder->with([$name]);
                            }
                        }

                        break;
                }
            }
        }

        return $builder;
    }

    /**
     * Caps a to-many relation include to its `limit` best-ranked rows *per parent row*, not
     * across the whole eager-loaded batch.
     *
     * Eloquent has no built-in way to do this for an ad-hoc `with([$name => fn ($sub) => ...])`
     * closure: `ofMany()` (Laravel's own "top-1-per-parent" mechanism) only works on relations
     * *defined* as `hasOne()`/`morphOne()` on the model, and a plain `->limit(N)` inside an
     * eager-load closure caps the *entire combined query* (all parents' rows pooled together),
     * not each parent's own slice — e.g. `limit(1)` across 10 parents would return exactly one
     * row total, attached to only one of them, not one row per parent.
     *
     * Instead of restructuring the query (which would require swapping out the `Relation`'s own
     * builder instance — not something a `with()` closure's return value can actually do, since
     * `Builder::eagerLoadRelation()` discards it and reads back `$relation`'s mutated builder
     * directly), this adds a correlated-subquery `WHERE` clause: for each row, count how many
     * sibling rows (same parent, same relation) outrank it per the already-applied `sort` order,
     * and keep only rows with fewer than `limit` rows ahead of them. This only ever *adds* a
     * `WHERE`, so it can't disturb the select/sort/eager-constraints already built onto $builder.
     *
     * @template TFilterModel of Model
     *
     * @param  Builder<TFilterModel>  $builder
     * @param  array<string, mixed>  $commonFilters
     * @return Builder<TFilterModel>
     */
    private function applyRelationLimit(Relation $relation, Builder $builder, array $commonFilters): Builder
    {
        $limit = (int) ($commonFilters['limit'] ?? 0);

        if ($limit <= 0 || ! $this->isToManyRelation($relation)) {
            return $builder;
        }

        $model = $builder->getModel();
        $table = $model->getTable();

        $correlateColumns = array_filter([
            $relation->getForeignKeyName(),
            method_exists($relation, 'getMorphType') ? $relation->getMorphType() : null,
        ]);
        if (collect($correlateColumns)->contains(fn ($column) => ! $this->isSafeIdentifier($column))) {
            // The relation's own key columns should always be safe (they come from model code,
            // not request input) — bail rather than risk building SQL off something unexpected.
            return $builder;
        }

        $orders = collect($builder->getQuery()->orders ?? [])
            ->filter(fn ($order) => isset($order['column'], $order['direction']) && is_string($order['column']))
            ->map(fn ($order) => [
                // A qualified `sort` (e.g. from a select alias) only ever arrives unqualified
                // here in practice, but strip any `table.` prefix defensively before validating.
                'column' => last(explode('.', $order['column'])),
                'direction' => strtolower((string) $order['direction']) === 'desc' ? 'desc' : 'asc',
            ])
            ->filter(fn ($order) => $this->isSafeIdentifier($order['column']))
            ->values()
            ->all();
        // A final tie-breaker on the primary key, so rows with identical sort values still get a
        // strict, stable order instead of an arbitrary/duplicate rank.
        $orders[] = ['column' => $model->getKeyName(), 'direction' => 'desc'];

        $correlation = collect($correlateColumns)
            ->map(fn ($column) => "`qb_rank`.`{$column}` = `{$table}`.`{$column}`")
            ->implode(' and ');

        $builder->whereRaw(
            "(select count(*) from `{$table}` as `qb_rank` where {$correlation} and ({$this->buildRankExpression($orders, $table)})) < ?",
            [$limit],
        );

        return $builder;
    }

    private function isToManyRelation(Relation $relation): bool
    {
        return $relation instanceof HasMany || $relation instanceof MorphMany;
    }

    /**
     * Only plain identifier characters — column names in `$orders` originate from the request's
     * `sort` input (which allows arbitrary strings, including a `raw:` SQL-expression escape
     * hatch handled upstream in `SingleSort`), so this is the one guard standing between that
     * input and a raw SQL string here. Reject rather than escape: this is a generic package used
     * for arbitrary models, so there's no safe way to know a caller *meant* something unusual.
     */
    private function isSafeIdentifier(string $column): bool
    {
        return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column);
    }

    /**
     * Builds a SQL boolean expression that is true when the `qb_rank`-aliased row sorts strictly
     * before the base table's row, per `$orders` (an ordered list of column => direction, most
     * significant first) — i.e. "how many rows outrank this one", one comparison term per sort
     * column with the preceding columns pinned equal, standard "greatest-N-per-group" shape.
     *
     * @param  array<int, array{column: string, direction: string}>  $orders
     */
    private function buildRankExpression(array $orders, string $table): string
    {
        $clauses = [];
        $equalTo = [];

        foreach ($orders as $order) {
            $column = $order['column'];
            $operator = $order['direction'] === 'desc' ? '>' : '<';

            $equality = collect($equalTo)->map(fn ($c) => "`qb_rank`.`{$c}` = `{$table}`.`{$c}`");
            $comparison = "`qb_rank`.`{$column}` {$operator} `{$table}`.`{$column}`";

            $clauses[] = $equality->isEmpty()
                ? "({$comparison})"
                : '('.$equality->implode(' and ')." and {$comparison})";

            $equalTo[] = $column;
        }

        return implode(' or ', $clauses);
    }
}
