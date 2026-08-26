<?php

namespace Domains\Core\Repositories;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Kalimulhaq\Qubuilder\Qubuilder;

class BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Model::class;

    /**
     * The query filter.
     */
    protected array $filter = [];

    /**
     * List the records with optional filter.
     *
     * The filter bag is handed to Qubuilder as-is against the (possibly
     * already-scoped) query from query() — select/filter/sort/include/group
     * are qubuilder's concern, not this method's.
     *
     * @param  array  $filter  The filter to apply when retrieving records.
     */
    public function list(array $filter = []): LengthAwarePaginator
    {
        $this->filter($filter);

        $builder = Qubuilder::make($this->filter, $this->query());

        return $builder->query()->paginate(
            perPage: $this->resolvePerPage($this->filter),
            page: $builder->page(),
        );
    }

    /**
     * Resolve the effective page size for list(). Missing or zero clamps to
     * the configured default; anything else clamps to the configured max.
     *
     * @param  array  $filter  The filter bag list() was called with.
     */
    protected function resolvePerPage(array $filter): int
    {
        $limit = Arr::get($filter, 'limit');

        if (! $limit) {
            return (int) config('qubuilder.limit.default');
        }

        $max = (int) config('qubuilder.limit.max');

        return $limit > 0 ? min((int) $limit, $max) : $max;
    }

    /**
     * Retrieve a single record by ID with optional filter.
     *
     * @param  mixed  $id  The ID of the record to retrieve.
     * @param  array  $filter  The filter to apply when retrieving the record.
     * @param  array  $columns  The columns to select.
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    public function get($id, array $filter = [], array $columns = ['*'])
    {
        $this->filter($filter);

        return Qubuilder::make($this->filter, $this->query())
            ->query()
            ->findOrFail($id, $columns);
    }

    /**
     * Retrieve a single record by ID with optional filter.
     *
     * @param  mixed  $id  The ID of the record to retrieve.
     * @param  array  $filter  The filter to apply when retrieving the record.
     * @param  array  $columns  The columns to select.
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    public function getTrashed($id, array $filter = [], array $columns = ['*'])
    {
        $this->filter($filter);

        return Qubuilder::make($this->filter, $this->query())
            ->query()
            ->onlyTrashed()
            ->findOrFail($id, $columns);
    }

    /**
     * Build the query based on current filters. Deliberately thin: the whole
     * filter bag is handed to QueryFilter as-is and it decides what to do with
     * each key — nothing here should be picking keys apart or duplicating that
     * decision-making.
     *
     * @return Builder
     */
    protected function query()
    {
        $query = $this->model::query();

        return $query;
    }

    /**
     * Create a new record.
     *
     * @param  array  $data  The data to create a new record with.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            return $this->model::create($data);
        });
    }

    /**
     * Bulk insert multiple records.
     *
     * @param  array  $data  Array of records to insert
     */
    public function bulkInsert(array $data): bool
    {
        return $this->dbTransaction(function () use ($data) {
            return $this->model::insert($data);
        });
    }

    /**
     * Update an existing record by ID.
     *
     * @param  mixed  $id  The ID of the record to update.
     * @param  array  $data  The data to update the record with.
     *
     * @throws ModelNotFoundException
     */
    public function update($id, array $data): Model
    {
        return $this->dbTransaction(function () use ($id, $data) {
            $record = $this->findOrFail($id);

            $record->update($data);

            return $record->refresh();
        });
    }

    /**
     * Delete a record by ID.
     *
     * @param  mixed  $id  The ID of the record to delete.
     * @return bool|null
     *
     * @throws ModelNotFoundException
     */
    public function delete($id)
    {
        return $this->dbTransaction(function () use ($id) {
            $record = $this->findOrFail($id);

            return $record->delete();
        });
    }

    /**
     * Find a record by ID or return the Model instance if provided.
     *
     * @param  int|Model  $id  The ID of the record or a Model instance.
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->model::findOrFail($id);
    }

    /**
     * Find a record by ID or return the Model instance if provided.
     *
     * @param  int|Model  $id  The ID of the record or a Model instance.
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    public function find(int|Model $id)
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->model::find($id);
    }

    /**
     * Find a record by a specific field and value or fail.
     *
     * @param  string  $field  The field to search by.
     * @param  mixed  $value  The value to search for.
     * @return Model
     *
     * @throws ModelNotFoundException
     */
    public function findOrFailBy(string $field, mixed $value)
    {
        return $this->model::where($field, $value)->firstOrFail();
    }

    /**
     * Find a record by a specific field and value.
     *
     * @param  string  $field  The field to search by.
     * @param  mixed  $value  The value to search for.
     * @return Model|null
     */
    public function findBy(string $field, mixed $value)
    {
        return $this->model::where($field, $value)->first();
    }

    /**
     * Set the query filter.
     *
     * @param  array  $filter  The filter to store for query()/list() to use.
     * @return $this
     */
    public function filter(array $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Append aggregate select columns and return the alias to order by (first aggregate).
     *
     * A single aggregate is aliased by its type (`count`, `sum`, ...) to preserve a flat
     * response shape; multiple aggregates are aliased `{type}_{field}`.
     *
     * NOTE: `field` and `type` must come from a whitelisted (Rule::in) request so they are
     * safe to interpolate into the raw expression.
     *
     * @param  array<int, string|Expression>  $select
     * @param  array<int, array{field: string, type: string}>  $aggregates
     */
    protected function applyAggregateSelects(array &$select, array $aggregates, string $table): string
    {
        $single = count($aggregates) === 1;
        $orderBy = '';

        foreach ($aggregates as $agg) {
            $type = strtolower($agg['type']);
            $field = $agg['field'];
            $alias = $single ? $type : "{$type}_{$field}";

            if ($orderBy === '') {
                $orderBy = $alias;
            }

            if ($type === 'count') {
                $select[] = DB::raw("$type($table.$field) AS `$alias`");
            } else {
                $select[] = DB::raw("CAST($type($table.$field) AS DECIMAL(20,2)) AS `$alias`");
            }
        }

        return $orderBy;
    }

    /**
     * Append a date-truncated group-by column (DATE / month / year) for a
     * `column|granularity` group_by entry. Falls back to grouping on the raw column for
     * unknown granularities.
     *
     * The `$field` carries the granularity suffix (e.g. `created_at|monthly`). The column
     * is qualified with `$table` unless it is already table-qualified (contains a dot); the
     * bare column name (after the last dot) is used as the select alias.
     *
     * @param  array<int, string|Expression>  $select
     * @param  array<int, string|Expression>  $groupBy
     */
    protected function applyDateGroup(array &$select, array &$groupBy, string $field, string $table): void
    {
        [$column, $granularity] = array_pad(explode('|', $field, 2), 2, '');
        $alias = Str::afterLast($column, '.');
        $col = str_contains($column, '.') ? $column : "$table.$column";

        switch ($granularity) {
            case 'date':
            case 'day':
            case 'daily':
                $expr = "DATE($col)";
                break;
            case 'month':
            case 'monthly':
                $expr = "DATE_FORMAT($col, '%Y-%m')";
                break;
            case 'year':
            case 'annually':
            case 'yearly':
                $expr = "YEAR($col)";
                break;
            default:
                $select[] = "$col AS $alias";
                $groupBy[] = $col;

                return;
        }

        $select[] = DB::raw("$expr AS $alias");
        $groupBy[] = DB::raw($expr);
    }

    /**
     * Group by a related record via a left join, selecting its id and any extra columns.
     *
     * e.g. applyRelationGroup($query, $select, $groupBy, 'division_id', 'divisions', 'deals', ['name'])
     * selects `divisions.id AS division_id` and `divisions.name AS division_name`, and left
     * joins `divisions` on `deals.division_id = divisions.id`. Each extra column is aliased
     * `{base}_{column}`, where `{base}` is `$foreignKey` without its `_id` suffix (so
     * `['code']` on `portal_id` yields `portal_code`).
     *
     * @param  array<int, string|Expression>  $select
     * @param  array<int, string|Expression>  $groupBy
     * @param  array<int, string>  $extraColumns
     */
    protected function applyRelationGroup(Builder $query, array &$select, array &$groupBy, string $foreignKey, string $relationTable, string $fkTable, array $extraColumns = []): void
    {
        $base = Str::beforeLast($foreignKey, '_id');

        $select[] = "$relationTable.id AS $foreignKey";
        $groupBy[] = "$relationTable.id";

        foreach ($extraColumns as $column) {
            $select[] = "$relationTable.$column AS {$base}_{$column}";
            $groupBy[] = "$relationTable.$column";
        }

        $query->leftJoin($relationTable, "$fkTable.$foreignKey", '=', "$relationTable.id");
    }

    /**
     * Sort assembled (in-memory) report rows by request sort criteria.
     *
     * Supports plain columns as well as nested aggregated dot-paths
     * (e.g. "activities.qualification.total") and multiple (hybrid) keys. Values are resolved
     * per row via data_get, so any dot-path present in the row is sortable. Directions are
     * case-insensitive; anything other than "desc" is treated as ascending.
     *
     * @param  Collection<int, mixed>  $rows
     * @param  array<string, string>  $sort  { "field": "asc"|"desc" }
     * @return Collection<int, mixed>
     */
    protected function sortReportRows(Collection $rows, array $sort): Collection
    {
        if (empty($sort)) {
            return $rows->values();
        }

        $criteria = collect($sort)
            ->map(fn ($dir, $key) => [$key, strtolower((string) $dir) === 'desc' ? 'desc' : 'asc'])
            ->values()
            ->all();

        return $rows->sortBy($criteria)->values();
    }

    protected function withCount(): array
    {
        return [];
    }

    public function dbTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}
