<?php

namespace Modules\Process\Services;

use Illuminate\Support\Arr;
use Infrastructure\BaseService;
use Infrastructure\Exceptions\CustomException;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Repositories\ObjectRepository;

class ObjectService extends BaseService
{
    /**
     * ObjectService constructor.
     * @param \Modules\Process\Repositories\ObjectRepository $repository
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get first object of multiple clusters in a process
     * @param $processId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getObjectsByProcess($processId)
    {
        $objects = $this->repository->getObjectsByProcess($processId);
        $objects = self::blendObjectsIdentity($objects);
        $objects = $this->assignAppearances($objects);

        return $objects;
    }

    /**
     * @param array $searchedObjects
     * @param bool $findWithProcess
     * @return mixed
     */
    public function getObjectsAfterSearchFace(array $searchedObjects, bool $findWithProcess)
    {
        $ids = Arr::pluck($searchedObjects, 'object_id');
        $objects = $this->repository->getObjectsAfterSearchFace($ids);
        $objects = self::blendObjectsIdentity($objects);
        $objects = $this->assignAppearances($objects, $findWithProcess);

        foreach ($objects as &$object) {
            $object->confidence_rate = null;
            foreach ($searchedObjects as $searchedObject) {
                if ($searchedObject->object_id == (string) $object->_id) {
                    $object->distance = $searchedObject->search_distance;
                }
            }
        }
        $objects = array_values(collect($objects)->sortBy('distance')->toArray());

        return $objects;
    }

    /**
     * @param $mongoIds
     * @return mixed
     */
    public function getFirstObjectsByMongoIds($mongoIds)
    {
        $objects = $this->repository->getFirstObjectsByMongoIds($mongoIds);
        $objects = self::blendObjectsIdentity($objects);

        return $objects;
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function getObjectsByIds($ids)
    {
        $objects = $this->repository->getObjectsByIds($ids);
        $objects = self::blendObjectsIdentity($objects);

        return $objects;
    }

    /**
     * @param $id
     * @return bool|\Infrastructure\Exceptions\CustomException|\Infrastructure\Exceptions\ResourceNotFoundException
     */
    public function startRendering($id)
    {
        $object = $this->repository->findById($id);

        if (!$object) {
            return new ResourceNotFoundException();
        }
        $url = config('app.ai_server') . '/processes/faces/rendering';
        $payload = ['object_id' => $object->idString];
        $response = $this->sendPOSTRequest($url, $payload, $this->getDefaultHeaders());

        if (!$response->status) {
            return new CustomException('AI_FAILED', $response->statusCode, (object)[
                'message' => $response->message,
            ]);
        }
        return true;
    }

    /**
     * Assign appearances to clustered objects (by one process or all processes)
     * @param $objects
     * @param bool $findWithProcess
     * @return mixed
     */
    public function assignAppearances($objects, $findWithProcess = true)
    {
        foreach ($objects as $object) {
            if (count($object->cluster_elements)) {
                $conditions = [
                    'cluster_elements.cluster' => $object->cluster_elements[0]->cluster->_id,
                ];
                if ($findWithProcess) {
                    $conditions['process'] = $object->process;
                }
                $object->appearances = $this->repository->getAppearances($conditions);
            } else {
                $object->appearances = [clone $object];
            }
        }
        return $objects;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getStatisticByProcesses(array $ids): array
    {
        return $this->repository->getStatisticByProcesses($ids);
    }

    /**
     * @param $objects
     * @return mixed
     */
    public static function blendObjectsIdentity($objects)
    {
        foreach ($objects as $object) {
            $object->identity = !empty($object->identity) ? $object->identity : ($object->cluster_elements[0]->cluster->identity ?? null);
        }
        return $objects;
    }

    protected function getAIUrl(string $mongoId = null)
    {
        return null;
    }
}
