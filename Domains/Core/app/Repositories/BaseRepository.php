<?php

namespace Domains\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Model::class;

    /**
     * The pagination record limit.
     */
    protected int $limit = 50;

    /**
     * Hard cap on the pagination limit — a caller-requested limit above this
     * (or zero/negative) clamps to this value instead of silently allowing an
     * unbounded page size.
     */
    protected int $maxLimit = 100;

    /**
     * The pagination page.
     */
    protected int $page = 1;

    /**
     * The query filter.
     */
    protected array $filter = [];

    /**
     * List the records with optional filter.
     *
     * @param  array  $filter  The filter to apply when retrieving records.
     * @param  array  $columns  The columns to select. Pass only what the consuming view
     *                          actually renders to keep the query and response minimal.
     * @return LengthAwarePaginator
     */
    public function list(array $filter = [], array $columns = ['*'])
    {
        $this->filter($filter);

        return $this->query()->paginate(perPage: $this->limit, page: $this->page, columns: $columns);
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

        return $this->query()->findOrFail($id, $columns);
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

        return $this->query()->onlyTrashed()->findOrFail($id, $columns);
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

            return $record;
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
     * Set the pagination limit. Clamps to $maxLimit — above it, or zero/negative,
     * both collapse to the cap rather than allowing an unbounded page size.
     *
     * @param  int  $limit  The field to set the limit.
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit > 0 ? min($limit, $this->maxLimit) : $this->maxLimit;

        return $this;
    }

    /**
     * Set the pagination page number.
     *
     * @param  int  $page  The field to set the pagination page number.
     * @return $this
     */
    public function page(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set the pagination page number.
     *
     * @param  array  $filter  The field to set the query filter.
     * @return $this
     */
    public function filter(array $filter)
    {
        $this->filter = $filter;

        if ($limit = Arr::get($this->filter, 'limit')) {
            $this->limit($limit);
        }

        if ($page = Arr::get($this->filter, 'page')) {
            $this->page($page);
        }

        return $this;
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
