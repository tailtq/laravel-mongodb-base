<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Modules\Process\Models\Process;
use App\Traits\AnalysisTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    use AnalysisTrait;

    /**
     * @return Factory|View
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
            ->select(
                array_merge(['id', 'camera_id', 'mongo_id', 'name'], $this->getStatistic('processes.id'))
            )
            ->orderBy('created_at')
            ->get();
    }
}
