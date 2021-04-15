<?php

namespace Infrastructure;

use Infrastructure\Traits\RequestAPI;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;

abstract class BaseService
{
    use RequestAPI;

    /**
     * @var BaseRepository $repository;
     */
    protected $repository;

    // View handler function
    /**
     * @param array $data
     * @return array|bool|CustomException|int
     */
    public function createAndSync(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @return CustomException|ResourceNotFoundException
     */
    public function update(array $data, string $id)
    {
        $item = $this->repository->findById($id);

        if (!$item) {
            return new ResourceNotFoundException();
        }

        return $this->repository->updateBy(['_id' => $id], $data);
    }

    /**
     * @param $id
     * @return CustomException|ResourceNotFoundException
     */
    public function delete($id)
    {
        $item = $this->repository->findById($id);

        if (!$item) {
            return new ResourceNotFoundException();
        }

        return $this->repository->deleteBy(['_id' => $id]);
    }

    // Base functions
    /**
     * @return mixed
     */
    public function paginate()
    {
        return $this->repository->listBy();
    }

    /**
     * @return Collection
     */
    public function listAll(): Collection
    {
        return $this->repository->listBy([], false);
    }

    /**
     * @param array|\Closure $condition
     * @param bool $shouldPaginate
     * @return Collection
     */
    public function listBy($condition, $shouldPaginate = false): Collection
    {
        return $this->repository->listBy($condition, $shouldPaginate);
    }

    /**
     * @param string $id
     * @return Model|ResourceNotFoundException
     */
    public function findById(string $id)
    {
        $item = $this->repository->findById($id);

        return $item ? $item : new ResourceNotFoundException();
    }

    /**
     * @param array|\Closure $condition
     * @param null $default
     * @return mixed
     */
    public function findBy($condition, $default = null)
    {
        $item = $this->repository->findBy($condition);

        return $item ? $item : ($default == 'error' ? new ResourceNotFoundException() : $default);
    }

    /**
     * @param array $data
     * @param bool $indexedReturningId
     * @return array|bool|int
     */
    public function create(array $data, $indexedReturningId = false)
    {
        return $this->repository->create($data, $indexedReturningId);
    }

    /**
     * @param array|\Closure $condition
     * @param array $data
     * @return mixed
     */
    public function updateBy($condition, array $data)
    {
        return $this->repository->updateBy($condition, $data);
    }

    /**
     * @param string $conditionColumn
     * @param array $data
     */
    public function updateMany(string $conditionColumn, array $data)
    {
        $this->repository->updateMany($conditionColumn, $data);
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function deleteBy($condition)
    {
        return $this->repository->deleteBy($condition);
    }
}
