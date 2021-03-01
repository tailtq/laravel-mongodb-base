<?php

namespace Modules\Process\Repositories;

use Illuminate\Support\Facades\DB;
use Infrastructure\BaseRepository;
use Modules\Process\Models\TrackedObject;

class ObjectRepository extends BaseRepository
{
    /**
     * ProcessRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(TrackedObject::class);
    }

    /**
     * @param $processId
     * @return mixed
     */
    public function getObjectsByProcess($processId)
    {
        // TODO: Add pagination

        return $this->model
            ->leftJoin('clusters', 'objects.cluster_id', 'clusters.id')
            ->leftJoin('identities as CI', 'clusters.identity_id', 'CI.id')
            ->leftJoin('identities as OI', 'objects.identity_id', 'OI.id')
            ->where('objects.process_id', $processId)
            ->whereIn('objects.id', function ($query) use ($processId) {
                $query->select(DB::raw('MIN(O.id)'))
                    ->from('objects AS O')
                    ->where('O.process_id', $processId)
                    ->groupBy(DB::raw('IFNULL(O.cluster_id, UUID())'));
            })
            ->select([
                'objects.*',
                'OI.id as identity_id',
                'OI.name as identity_name',
                'OI.images as identity_images',
                'CI.id as cluster_identity_id',
                'CI.name as cluster_identity_name',
                'CI.images as cluster_identity_images',
            ])
            ->get();
    }

    /**
     * @param array $objectMongoIds
     * @return mixed
     */
    public function getObjectsAfterSearchFace(array $objectMongoIds)
    {
        return $this->model
            ->join('processes', 'objects.process_id', 'processes.id')
            ->leftJoin('clusters', 'objects.cluster_id', 'clusters.id')
            ->leftJoin('identities as CI', 'clusters.identity_id', 'CI.id')
            ->leftJoin('identities as OI', 'objects.identity_id', 'OI.id')
            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
                $query->select(DB::raw('MIN(O.id)'))
                    ->from('objects AS O')
                    ->whereIn('O.mongo_id', $objectMongoIds)
                    ->groupBy(DB::raw('IFNULL(O.cluster_id, UUID())'));
            })
            ->select([
                'objects.*',
                'OI.id as identity_id',
                'OI.name as identity_name',
                'OI.images as identity_images',
                'CI.id as cluster_identity_id',
                'CI.name as cluster_identity_name',
                'CI.images as cluster_identity_images',
                'processes.name as process_name',
            ])
            ->get();
    }
}
