<?php

namespace App\Repositories;

use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * @return array<int, string>
     */
    abstract public function getFieldsSearchable(): array;

    abstract public function model(): string;

    /**
     * @throws Exception
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function paginate(int $perPage, array|string $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, columns: is_array($columns) ? $columns : [$columns]);
    }

    /**
     * @param  array<string, mixed>  $search
     */
    public function allQuery(array $search = [], ?int $skip = null, ?int $limit = null): Builder
    {
        $query = $this->model->newQuery();

        foreach ($search as $key => $value) {
            if (in_array($key, $this->getFieldsSearchable(), true)) {
                $query->where($key, $value);
            }
        }

        if ($skip !== null) {
            $query->skip($skip);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $search
     * @param  array<int, string>|string  $columns
     * @return Collection<int, Model>
     */
    public function all(array $search = [], ?int $skip = null, ?int $limit = null, array|string $columns = ['*']): Collection
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get(columns: is_array($columns) ? $columns : [$columns]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): Model
    {
        $model = $this->model->newInstance($input);
        $model->save();

        return $model;
    }

    /**
     * @param  array<int, string>|string  $columns
     */
    public function find(int|string $id, array|string $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->find($id, columns: is_array($columns) ? $columns : [$columns]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function update(array $input, int|string $id): Model
    {
        $model = $this->model->newQuery()->findOrFail($id);
        $model->fill($input);
        $model->save();

        return $model;
    }

    public function delete(int|string $id): bool
    {
        $model = $this->model->newQuery()->findOrFail($id);

        return (bool) $model->delete();
    }

    public function findWithoutFail(int|string $id, array|string $columns = ['*']): ?Model
    {
        try {
            return $this->find($id, $columns);
        } catch (Exception) {
            return null;
        }
    }
}
