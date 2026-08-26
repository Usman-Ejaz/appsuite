<?php

namespace Domains\Identity\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Identity\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CompanyRepository extends BaseRepository
{
    /**
     * The model associated with the repository.
     */
    protected string $model = Company::class;

    /**
     * Create a new company along with the apps it's subscribed to from the start.
     *
     * @param  array  $data  The data to create a new record with.
     */
    public function create(array $data): Model
    {
        return $this->dbTransaction(function () use ($data) {
            $appIds = Arr::pull($data, 'apps', []);

            $company = parent::create($data);

            $company->apps()->attach($appIds, [
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);

            return $company->load('apps');
        });
    }

    /**
     * Update a company, optionally replacing which apps it's subscribed to. Apps already
     * attached and still present in the new list keep their original assigned_by/assigned_at —
     * only newly attached apps are stamped with the acting user and the current time.
     *
     * @param  mixed  $id  The ID of the record to update.
     * @param  array  $data  The data to update the record with.
     */
    public function update($id, array $data): Model
    {
        return $this->dbTransaction(function () use ($id, $data) {
            $appIds = Arr::pull($data, 'apps');

            $company = parent::update($id, $data);

            if ($appIds !== null) {
                $currentAppIds = $company->apps()->pluck('apps.id')->all();

                $company->apps()->detach(array_diff($currentAppIds, $appIds));

                $company->apps()->attach(array_diff($appIds, $currentAppIds), [
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
            }

            return $company->load('apps');
        });
    }
}
