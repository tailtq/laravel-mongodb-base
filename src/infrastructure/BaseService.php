<?php

namespace Infrastructure;

use App\Traits\RequestAPI;
use Illuminate\Support\Facades\Log;
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

    public function paginate()
    {
        return $this->repository->listBy();
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
     * @param array $data
     * @return \Infrastructure\Exceptions\CustomException
     */
    public function create(array $data)
    {
        // Proceed AI request
        $response = $this->sendPOSTRequest($this->getAIUrl(), $data, $this->getDefaultHeaders());
        if (!$response->status) {
            Log::error('AI FAILED ' . json_encode($response->message));
            
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
            Log::error('AI FAILED ' . json_encode($response->message));
            
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
            Log::error('AI FAILED ' . json_encode($response->message));

            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }

        return $this->repository->deleteBy(['id' => $id]);
    }
}
