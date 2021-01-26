<?php

namespace App\Traits;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait AnalysisTrait
{
    public function getStatistic($processId)
    {
        return [
            DB::raw("(SELECT COUNT(*) FROM objects WHERE objects.process_id = $processId) as total_appearances"),
            DB::raw("
                (SELECT (SELECT COUNT(*) FROM objects as OO WHERE OO.id in (
                    SELECT min(objects.id)
                        FROM objects
                        WHERE objects.process_id = $processId
                        AND objects.cluster_id IS NOT NULL
                        GROUP BY objects.cluster_id
                )) + (SELECT COUNT(*) FROM objects WHERE process_id = $processId AND cluster_id IS NULL)) AS total_objects
            "),  # group by uuid is computationally expensive than combination of 2 select count
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $processId AND clusters.identity_id IS NOT NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                ) OR (OO.cluster_id IS NULL AND OO.identity_id IS NOT NULL AND OO.process_id = $processId)) as total_identified
            "),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $processId AND clusters.identity_id IS NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                ) OR (OO.cluster_id IS NULL AND OO.identity_id IS NULL AND OO.process_id = $processId)) as total_unidentified
            ")
        ];
    }

    public function getAppearances($objects)
    {
        foreach ($objects as $object) {
            if ($object->cluster_id) {
                $object->appearances = DB::table('objects')
                    ->where('cluster_id', $object->cluster_id)
                    ->where('process_id', $object->process_id)
                    ->orderBy('track_id')
                    ->get();
            } else {
                $object->appearances = [clone $object];
            }
        }

        return $objects;
    }
}
