<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Modules\Process\Services\ProcessService;

class MonitorController extends Controller
{
    private $processService;

    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
    }

    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('pages.monitors.index', [
            'processes' => $this->processService->getDetectingProcess()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewProcesses(Request $request): \Illuminate\Http\JsonResponse
    {
        $ignoredIds = $request->get('ignored_ids');
        $processes = $this->processService->getDetectingProcess($ignoredIds);

        return $this->success($processes);
    }
}
