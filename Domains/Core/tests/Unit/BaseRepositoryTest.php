<?php

use Domains\Core\Models\App;
use Domains\Core\Repositories\BaseRepository;
use Illuminate\Database\Query\Expression;
use Tests\TestCase;

uses(TestCase::class);

function baseRepositoryHelperProbe(): BaseRepository
{
    return new class extends BaseRepository
    {
        public function callApplyAggregateSelects(array &$select, array $aggregates, string $table): string
        {
            return $this->applyAggregateSelects($select, $aggregates, $table);
        }

        public function callApplyDateGroup(array &$select, array &$groupBy, string $field, string $table): void
        {
            $this->applyDateGroup($select, $groupBy, $field, $table);
        }

        public function callApplyRelationGroup($query, array &$select, array &$groupBy, string $foreignKey, string $relationTable, string $fkTable, array $extraColumns = []): void
        {
            $this->applyRelationGroup($query, $select, $groupBy, $foreignKey, $relationTable, $fkTable, $extraColumns);
        }

        public function callSortReportRows($rows, array $sort)
        {
            return $this->sortReportRows($rows, $sort);
        }
    };
}

function expressionValue(Expression $expression): string
{
    return (new ReflectionProperty($expression, 'value'))->getValue($expression);
}

test('applyAggregateSelects aliases a single aggregate by its type', function () {
    $select = [];

    $orderBy = baseRepositoryHelperProbe()->callApplyAggregateSelects($select, [
        ['field' => 'amount', 'type' => 'sum'],
    ], 'deals');

    expect($orderBy)->toBe('sum')
        ->and($select)->toHaveCount(1)
        ->and(expressionValue($select[0]))->toBe('CAST(sum(deals.amount) AS DECIMAL(20,2)) AS `sum`');
});

test('applyAggregateSelects aliases multiple aggregates by type_field and uses a plain count expression', function () {
    $select = [];

    $orderBy = baseRepositoryHelperProbe()->callApplyAggregateSelects($select, [
        ['field' => 'amount', 'type' => 'sum'],
        ['field' => 'id', 'type' => 'count'],
    ], 'deals');

    expect($orderBy)->toBe('sum_amount')
        ->and($select)->toHaveCount(2)
        ->and(expressionValue($select[1]))->toBe('count(deals.id) AS `count_id`');
});

test('applyDateGroup truncates by month and by year', function () {
    $select = [];
    $groupBy = [];
    baseRepositoryHelperProbe()->callApplyDateGroup($select, $groupBy, 'created_at|monthly', 'deals');

    expect(expressionValue($select[0]))->toBe("DATE_FORMAT(deals.created_at, '%Y-%m') AS created_at")
        ->and(expressionValue($groupBy[0]))->toBe("DATE_FORMAT(deals.created_at, '%Y-%m')");

    $select = [];
    $groupBy = [];
    baseRepositoryHelperProbe()->callApplyDateGroup($select, $groupBy, 'created_at|yearly', 'deals');

    expect(expressionValue($select[0]))->toBe('YEAR(deals.created_at) AS created_at');
});

test('applyDateGroup falls back to the raw column for an unknown or missing granularity', function () {
    $select = [];
    $groupBy = [];
    baseRepositoryHelperProbe()->callApplyDateGroup($select, $groupBy, 'status', 'deals');

    expect($select)->toBe(['deals.status AS status'])
        ->and($groupBy)->toBe(['deals.status']);
});

test('applyRelationGroup selects the relation id plus extra columns and left joins it', function () {
    $select = [];
    $groupBy = [];
    $query = App::query();

    baseRepositoryHelperProbe()->callApplyRelationGroup($query, $select, $groupBy, 'company_id', 'companies', 'apps', ['name']);

    expect($select)->toBe(['companies.id AS company_id', 'companies.name AS company_name'])
        ->and($groupBy)->toBe(['companies.id', 'companies.name'])
        ->and($query->getQuery()->joins)->toHaveCount(1);

    $join = $query->getQuery()->joins[0];
    expect($join->type)->toBe('left')->and($join->table)->toBe('companies');
});

test('sortReportRows sorts in-memory rows by the requested criteria, defaulting unknown directions to ascending', function () {
    $rows = collect([
        ['name' => 'B', 'total' => 5],
        ['name' => 'A', 'total' => 10],
    ]);

    $sorted = baseRepositoryHelperProbe()->callSortReportRows($rows, ['total' => 'desc']);

    expect($sorted->pluck('name')->all())->toBe(['A', 'B']);

    $sorted = baseRepositoryHelperProbe()->callSortReportRows($rows, ['name' => 'sideways']);

    expect($sorted->pluck('name')->all())->toBe(['A', 'B']);
});

test('sortReportRows returns the rows unchanged when no sort is requested', function () {
    $rows = collect([['name' => 'B'], ['name' => 'A']]);

    expect(baseRepositoryHelperProbe()->callSortReportRows($rows, [])->pluck('name')->all())->toBe(['B', 'A']);
});
