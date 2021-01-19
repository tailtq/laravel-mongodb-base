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
                (SELECT COUNT(*) FROM objects as OO WHERE OO.id in (
                    SELECT min(objects.id) as unq_identity_id
                        FROM objects
                        WHERE objects.process_id = $processId
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_objects
            "),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $processId AND clusters.identity_id != NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_identified
            "),
            DB::raw("
                (SELECT COUNT(*) FROM objects as OO WHERE id in (
                    SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                        WHERE objects.process_id = $processId AND clusters.identity_id = NULL
                        GROUP BY IFNULL(objects.cluster_id, UUID())
                )) as total_unidentified
            ")
        ];
    }
}
