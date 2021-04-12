<?php

namespace Modules\Process\Repositories;

use Illuminate\Support\Facades\DB;
use Infrastructure\BaseRepository;
use Modules\Process\Models\TrackedObject;
use MongoDB\BSON\ObjectId;

class ObjectRepository extends BaseRepository
{
    protected $generalGroupingFields = [
        '_id' => '$_id',
        'identity' => ['$first' => '$identity'],
        'process' => ['$first' => '$process'],
        'track_id' => ['$first' => '$track_id'],
        'body_ids' => ['$first' => '$body_ids'],
        'face_ids' => ['$first' => '$face_ids'],
        'avatars' => ['$first' => '$avatars'],
        'confidence_rate' => ['$first' => '$confidence_rate'],
        'from_frame' => ['$first' => '$from_frame'],
        'from_time' => ['$first' => '$from_time'],
        'to_frame' => ['$first' => '$to_frame'],
        'to_time' => ['$first' => '$to_time'],
        'cluster_elements' => ['$push' => '$cluster_elements'],
    ];

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
        return $this->queryWithGeneralInfo([
            'process' => $processId,
            '$or' => [
                ['cluster_elements.ref_object' => null],
                ['cluster_elements' => null]
            ],
        ]);
    }

    /**
     * @param $condition
     * @return array
     */
    public function getAppearances($condition): array
    {
        return $this->aggregate([
            ['$lookup' => [
                'from' => 'cluster_elements',
                'localField' => '_id',
                'foreignField' => 'object',
                'as' => 'cluster_elements',
            ]],
            ['$unwind' => [
                'path' => '$cluster_elements',
                'preserveNullAndEmptyArrays' => True
            ]],
            ['$match' => $condition],
            ['$group' => $this->generalGroupingFields],
            ['$sort' => [
                'track_id' => 1
            ]]
        ]);
    }

    /**
     * @param array $objectMongoIds
     * @return mixed
     */
    public function getObjectsAfterSearchFace(array $ids)
    {
//        $columns = array_merge($this->generalInfoColumns, [
//            'processes.name as process_name',
//        ]);

        $ids = array_map(function ($id) {
            return new ObjectId($id);
        }, $ids);

        return $this->queryWithGeneralInfo(['_id' => ['$in' => $ids]]);
    }

    /**
     * @param $objectMongoIds
     * @return mixed
     */
    public function getFirstObjectsByMongoIds($objectMongoIds)
    {
        return $this->joinGeneralTables()
            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
                $query->select([DB::raw('MIN(id)')])
                    ->from('objects')
                    ->whereIn('mongo_id', $objectMongoIds)
                    ->groupBy(['cluster_id', 'process_id']);
            })
            ->select($this->generalInfoColumns)
            ->get();
    }

    public function getObjectsByIds($ids)
    {
        return $this->joinGeneralTables()
            ->whereIn('objects.id', $ids)
            ->select($this->generalInfoColumns)
            ->get();
    }

    /**
     * @param $conditions
     * @param array $additionalOperations
     * @return array
     */
    protected function queryWithGeneralInfo($conditions, array $additionalOperations = []): array
    {
        return $this->aggregate(array_merge($additionalOperations, [
            ['$lookup' => [
                'from' => 'identities',
                'localField' => 'identity',
                'foreignField' => '_id',
                'as' => 'identity'
            ]],
            ['$unwind' => [
                'path' => '$identity',
                'preserveNullAndEmptyArrays' => True
            ]],
            ['$lookup' => [
                'from' => 'cluster_elements',
                'let' => ['object_id' => '$_id'],
                'pipeline' => [
                    ['$match' => ['$expr' => ['$eq' => ['$object', '$$object_id']]]],
                    ['$lookup' => [
                        'from' => 'clusters',
                        'let' => ['cluster_id' => '$cluster'],
                        'pipeline' => [
                            ['$match' => ['$expr' => ['$eq' => ['$_id', '$$cluster_id']]]],
                            ['$lookup' => [
                                'from' => 'identities',
                                'let' => ['identity_id' => '$identity'],
                                'pipeline' => [
                                    ['$match' => ['$expr' => ['$eq' => ['$_id', '$$identity_id']]]],
                                ],
                                'as' => 'identity'
                            ]],
                            ['$unwind' => [
                                'path' => '$identity',
                                'preserveNullAndEmptyArrays' => True
                            ]],
                        ],
                        'as' => 'cluster'
                    ]],
                    ['$unwind' => [
                        'path' => '$cluster',
                        'preserveNullAndEmptyArrays' => True
                    ]]
                ],
                'as' => 'cluster_elements'
            ]],
            ['$unwind' => [
                'path' => '$cluster_elements',
                'preserveNullAndEmptyArrays' => True
            ]],
            ['$match' => $conditions],
            ['$group' => $this->generalGroupingFields],
            ['$sort' => [
                'track_id' => 1
            ]]
        ]));
    }
}
