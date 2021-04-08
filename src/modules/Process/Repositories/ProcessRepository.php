<?php

namespace Modules\Process\Repositories;

use Illuminate\Support\Facades\DB;
use Infrastructure\BaseRepository;
use Modules\Process\Models\Process;

class ProcessRepository extends BaseRepository
{
    /**
     * ProcessRepository constructor.
     */
    public function __construct()
    {
        parent::__construct(Process::class);
    }

    /**
     * @param $id
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getDetailWithStatistic($id, array $relations = [])
    {
        $columns = [
            '*',
//            DB::raw("(SELECT COUNT(*) FROM objects WHERE objects.process_id = $id) as total_appearances"),
//            DB::raw("
//                (SELECT (SELECT COUNT(*) FROM objects as OO WHERE OO.id in (
//                    SELECT min(objects.id)
//                        FROM objects
//                        WHERE objects.process_id = $id
//                        AND objects.cluster_id IS NOT NULL
//                        GROUP BY objects.cluster_id
//                )) + (SELECT COUNT(*) FROM objects WHERE process_id = $id AND cluster_id IS NULL)) AS total_objects
//            "),  # group by uuid is computationally expensive than combination of 2 select count
//            DB::raw("
//                (SELECT COUNT(*) FROM objects as OO WHERE id in (
//                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
//                        WHERE objects.process_id = $id AND clusters.identity_id IS NOT NULL
//                        GROUP BY IFNULL(objects.cluster_id, UUID())
//                ) OR (OO.cluster_id IS NULL AND OO.identity_id IS NOT NULL AND OO.process_id = $id)) as total_identified
//            "),
//            DB::raw("
//                (SELECT COUNT(*) FROM objects as OO WHERE id in (
//                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
//                        WHERE objects.process_id = $id AND clusters.identity_id IS NULL
//                        GROUP BY IFNULL(objects.cluster_id, UUID())
//                ) OR (OO.cluster_id IS NULL AND OO.identity_id IS NULL AND OO.process_id = $id)) as total_unidentified
//            ")
        ];

        return $this->findById($id, $columns, $relations);
    }
}
