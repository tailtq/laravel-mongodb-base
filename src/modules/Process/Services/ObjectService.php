<?php

namespace Modules\Process\Services;

use App\Helpers\DatabaseHelper;
use Illuminate\Support\Facades\DB;
use Infrastructure\BaseService;
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
     * Get appearances which were clustered (by one process or all processes)
     * @param $objects
     * @param bool $findWithProcess
     * @return mixed
     */
    public function assignAppearances($objects, $findWithProcess = true)
    {
        foreach ($objects as $object) {
            if ($object->cluster_id) {
                $object->appearances = $this->repository->listBy(function ($query) use ($object, $findWithProcess) {
                    $query = $query->where('cluster_id', $object->cluster_id);
                    if ($findWithProcess) {
                        $query = $query->where('process_id', $object->process_id);
                    }
                    return $query;
                }, false);
            } else {
                $object->appearances = [clone $object];
            }
        }
        return $objects;
    }

    /**
     * @param $objects
     * @return mixed
     */
    public static function blendObjectsIdentity($objects)
    {
        foreach ($objects as &$object) {
            $object->identity_id = $object->identity_id ?: $object->cluster_identity_id;
            $object->identity_name = $object->identity_name ?: $object->cluster_identity_name;
            $object->identity_images = $object->identity_images ?: $object->cluster_identity_images;
        }
        return $objects;
    }

    protected function getAIUrl(string $mongoId = null)
    {
        return null;
    }
}
