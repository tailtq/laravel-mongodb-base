<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('pages.monitors.index', [
            'processes' => $this->getDetectingProcesses()
        ]);
    }

    public function getNewProcesses(Request $request)
    {
        $ignoredIds = $request->get('ignored_ids');
        $processes = $this->getDetectingProcesses($ignoredIds);

        return $this->success($processes);
    }

    private function getDetectingProcesses($ignoredIds = [])
    {
        return DB::table('processes')
            ->whereNotIn('id', $ignoredIds)
            ->where('status', Process::STATUS['detecting'])
            ->select([
                'id',
                'camera_id',
                'mongo_id',
                'name',
                DB::raw("(SELECT COUNT(*) FROM objects WHERE objects.process_id = processes.id) as total_appearances"),
                DB::raw("
                    (SELECT COUNT(*) FROM objects as OO WHERE OO.id in (
                        SELECT min(objects.id) as unq_identity_id
                            FROM objects
                            WHERE objects.process_id = processes.id
                            GROUP BY IFNULL(objects.cluster_id, UUID())
                    )) as total_objects
                "),
                DB::raw("
                    (SELECT COUNT(*) FROM objects as OO WHERE id in (
                        SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                            WHERE objects.process_id = processes.id AND clusters.identity_id != NULL
                            GROUP BY IFNULL(objects.cluster_id, UUID())
                    )) as total_identified
                "),
                DB::raw("
                    (SELECT COUNT(*) FROM objects AS OO WHERE id IN (
                        SELECT min(objects.id) FROM objects INNER JOIN clusters ON clusters.id = objects.cluster_id
                            WHERE objects.process_id = processes.id AND clusters.identity_id = NULL
                            GROUP BY IFNULL(objects.cluster_id, UUID())
                    ) OR (OO.identity_id = NULL AND OO.cluster_id = NULL)) as total_unidentified
                ")
            ])
            ->orderBy('created_at')
            ->get();
    }
}
