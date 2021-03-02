<?php

namespace Infrastructure;

use App\Traits\RequestAPI;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;

abstract class BaseService
{
    use RequestAPI;

    /**
     * @var \Infrastructure\BaseRepository $repository;
     */
    protected $repository;

    abstract protected function getAIUrl(string $mongoId = null);

    // View handler function
    /**
     * @param array $data
     * @return array|bool|\Infrastructure\Exceptions\CustomException|int
     */
    public function createAndSync(array $data)
    {
        // Proceed AI request
        $response = $this->sendPOSTRequest($this->getAIUrl(), $data, $this->getDefaultHeaders());
        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        return $this->repository->create(array_merge($data, [
            'mongo_id' => $response->body->_id
        ]));
    }

    /**
     * @param array $data
     * @param $id
     * @return \Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function update(array $data, int $id)
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return new ResourceNotFoundException();
        }
        // Proceed AI request
        $response = $this->sendPUTRequest($this->getAIUrl($item->mongo_id), $data, $this->getDefaultHeaders());
        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        return $this->repository->updateBy(['id' => $id], $data);
    }

    /**
     * @param $id
     * @return \Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function delete($id)
    {
        $item = $this->repository->findById($id);
        if (!$item) {
            return new ResourceNotFoundException();
        }
        // Proceed AI request
        $response = $this->sendDELETERequest($this->getAIUrl($item->mongo_id), [], $this->getDefaultHeaders());
        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }

        return $this->repository->deleteBy(['id' => $id]);
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
     * @return \Illuminate\Support\Collection
     */
    public function listAll()
    {
        return $this->repository->listBy([], false);
    }

    /**
     * @param array|\Closure $condition
     * @param bool $shouldPaginate
     * @return \Illuminate\Support\Collection
     */
    public function listBy($condition, $shouldPaginate = false)
    {
        return $this->repository->listBy($condition, $shouldPaginate);
    }

    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function findById(int $id)
    {
        $item = $this->repository->findById($id);

        return $item ? $item : new ResourceNotFoundException();
    }

    /**
     * @param array|\Closure $condition
     * @return mixed
     */
    public function findBy($condition)
    {
        $item = $this->repository->findBy($condition);

        return $item ? $item : new ResourceNotFoundException();
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
