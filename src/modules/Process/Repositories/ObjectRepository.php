<?php

namespace Modules\Process\Repositories;

use Illuminate\Support\Facades\DB;
use Infrastructure\BaseRepository;
use Modules\Process\Models\TrackedObject;
use MongoDB\BSON\ObjectId;

class ObjectRepository extends BaseRepository
{
    protected $groupingFieldsByPerson = [
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
    public function listObjectsByProcess($processId)
    {
        return $this->aggregate($this->getFirstAppearancePeopleQuery(['process' => $processId]));
    }

    /**
     * @param array $ids
     * @return array
     */
    public function listFirstObjectsByIds(array $ids): array
    {
        return $this->aggregate($this->getFirstAppearancePeopleQuery(['_id' => ['$in' => $ids]]));
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
            ['$sort' => [
                'track_id' => 1
            ]],
            ['$group' => $this->groupingFieldsByPerson],
            ['$sort' => [
                'track_id' => 1
            ]],
        ]);
    }
    /**
     * Break this main query down for reusing in multiple places
     * @param array $conditions
     * @param bool $identityType
     * @return array
     */
    protected function getFirstAppearancePeopleQuery(array $conditions, $identityType = false): array
    {
        $conditions['$or'] = [
            ['cluster_elements.ref_object' => null],
            ['cluster_elements' => null]
        ];
        $identityCondition = [];

        if ($identityType == 'no_identity') {
            $identityCondition['identity'] = ['$exists' => false];
            $conditions = array_merge($conditions, $identityCondition);
        }
        [$groupingField1, $groupingField2] = $this->getDuringGroupingFields();

        return array_merge([
            ['$lookup' => [
                'from' => 'identities',
                'localField' => 'identity',
                'foreignField' => '_id',
                'as' => 'identity',
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
                            ['$match' => array_merge(['$expr' => ['$eq' => ['$_id', '$$cluster_id']]], $identityCondition),
                                // identity condition here
                            ],
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
            ['$sort' => [
                'track_id' => 1
            ]],
            ['$group' => $groupingField1],
            ['$group' => $groupingField2],
            ['$sort' => [
                'track_id' => 1
            ]],
        ]);
    }

    /**
     * @param array $processIds
     * @return array
     */
    public function getStatisticByProcesses(array $processIds): array
    {
        $condition = ['process' => ['$in' => $processIds]];
        $batches = [
            $this->aggregate([
                ['$match' => $condition],
                ['$group' => [
                    '_id' => '$process',
                    'total_appearances' => ['$sum' => 1]
                ]],
            ]),
            $this->aggregate(array_merge($this->getFirstAppearancePeopleQuery($condition), [
                ['$group' => [
                    '_id' => '$process',
                    'total_objects' => ['$sum' => 1]
                ]],
            ])),
            $this->aggregate(array_merge($this->getFirstAppearancePeopleQuery($condition, 'no_identity'), [
                ['$group' => [
                    '_id' => '$process',
                    'total_unidentified' => ['$sum' => 1]
                ]],
            ])),
        ];
        $newFormats = [];

        // Map keys of 3 queries together
        foreach ($batches as $batch) {
            foreach($batch as $process) {
                $id = null;

                foreach ((array)$process as $key => $value) {
                    if ($key == '_id') {
                        $id = $value = (string) $value;
                        $newFormats[$id] = empty($newFormats[$id]) ? [] : $newFormats[$id];
                    } else if ($key == 'total_unidentified') {
                        $newFormats[$id]['total_identified'] = $newFormats[$id]['total_objects'] - $value;
                    }
                    $newFormats[$id][$key] = $value;
                }
            }
        }
        // Initialize statistic for processes having no objects
        foreach ($processIds as $processId) {
            $key = (string) $processId;

            if (!empty($newFormats[$key])) {
                continue;
            }
            $newFormats[$key] = [
                '_id' => $key,
                'total_appearances' => 0,
                'total_objects' => 0,
                'total_identified' => 0,
                'total_unidentified' => 0,
            ];
        }

        return array_values($newFormats);
    }

    /**
     * Regrouping for complex query
     * @return array[]
     */
    public function getDuringGroupingFields(): array
    {
        $newFields = $this->groupingFieldsByPerson;
        $newFields['_id'] = ['$ifNull' => ['$cluster_elements.cluster._id', '$_id']];
        $newFields['original_id'] = ['$first' => '$_id'];

        $newFields2 = $this->groupingFieldsByPerson;
        $newFields2['_id'] = '$original_id';
        $newFields2['cluster_elements'] = ['$first' => '$cluster_elements'];
        $newFields2['original_id'] = ['$first' => '$original_id'];

        return [$newFields, $newFields2];
    }
}
