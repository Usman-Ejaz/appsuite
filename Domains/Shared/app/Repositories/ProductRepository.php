<?php

namespace Domains\Shared\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Shared\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductRepository extends BaseRepository
{
    protected string $model = Product::class;

    protected ?string $appCode = null;

    /**
     * Scope every subsequent query/create/update/delete on this repository
     * instance to a single app. Fluent so it can be called inline from the
     * controller, e.g. `$this->products->forAppCode($appCode)->list(...)`.
     */
    public function forAppCode(string $appCode): static
    {
        $this->appCode = $appCode;

        return $this;
    }

    protected function query(): Builder
    {
        $query = parent::query();

        if ($this->appCode) {
            $query->where('app_code', $this->appCode);
        }

        return $query;
    }

    /**
     * Stamps every product created through this repository with the scoped
     * app — never accepted from the request payload.
     */
    public function create(array $data): Model
    {
        $data = [
            ...$data,
            'app_code' => $this->appCode,
        ];

        return parent::create($data);
    }
}
