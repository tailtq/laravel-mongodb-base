<?php

namespace Modules\Process\Commands;

use Illuminate\Support\Facades\Log;
use Modules\Process\Events\AnalysisProceeded;
use Modules\Process\Events\ClusteringProceeded;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;
use MongoDB\BSON\ObjectId;

class ListenClusteringProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-clustering-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen clustering process from AI server';

    /**
     * @var ProcessService $processService
     */
    protected $processService;

    /**
     * @var ObjectService $objectService
     */
    protected $objectService;

    protected $fractal;

    /**
     * Create a new command instance.
     *
     * @param ProcessService $processService
     * @param ObjectService $objectService
     */
    public function __construct(
        ProcessService $processService,
        ObjectService $objectService
    ) {
        parent::__construct();
        $this->processService = $processService;
        $this->objectService = $objectService;
    }

    /**
     * Listen events
     */
    public function handle()
    {
        Redis::subscribe('clustering', function ($clusters) {
            Log::info("Clustering ---------------------- " . $clusters);
            $clusters = json_decode($clusters);

            if (!$clusters) {
                return;
            }
            $totalIds = [];

            foreach ($clusters as $cluster) {
                $ids = Arr::pluck($cluster->objects, 'object');
                $ids = array_map(function ($id) {
                    return new ObjectId($id);
                }, $ids);
                $totalIds = array_merge($totalIds, $ids);
            }
            $objects = $this->objectService->listFirstObjectsByIds($totalIds);
            $this->publishClusteringDataToEachProcess($objects);
        });
    }

    /**
     * @param array $objects
     */
    public function publishClusteringDataToEachProcess(array $objects)
    {
        $processesNewFormat = [];
        $processes = collect($objects)->groupBy('process');

        foreach ($processes as $processId => $groupedObjects) {
            $processIdString = (string) $processId;

            $process = $this->processService->getProcessDetail($processId);
            $processesNewFormat[] = $process;

            // publish objects to process detail
            broadcast(new ClusteringProceeded($process, $groupedObjects, "process.$processIdString.cluster"));
        }
        // publish statistical numbers to monitoring page
        broadcast(new AnalysisProceeded($processesNewFormat));
    }
}
