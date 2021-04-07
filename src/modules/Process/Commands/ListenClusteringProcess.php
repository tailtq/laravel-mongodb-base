<?php

namespace Modules\Process\Commands;

use Modules\Identity\Services\IdentityService;
use Modules\Process\Events\AnalysisProceeded;
use Modules\Process\Events\ClusteringProceeded;
use App\Traits\AnalysisTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ClusterService;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;

class ListenClusteringProcess extends Command
{
    use AnalysisTrait;

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
     * @var IdentityService $identityService
     */
    protected $identityService;

    /**
     * @var ProcessService $processService
     */
    protected $processService;

    /**
     * @var ClusterService $clusterService
     */
    protected $clusterService;

    /**
     * @var ObjectService $objectService
     */
    protected $objectService;

    /**
     * Create a new command instance.
     *
     * @param IdentityService $identityService
     * @param ProcessService $processService
     * @param ClusterService $clusterService
     * @param ObjectService $objectService
     */
    public function __construct(
        IdentityService $identityService,
        ProcessService $processService,
        ClusterService $clusterService,
        ObjectService $objectService
    ) {
        parent::__construct();
        $this->identityService = $identityService;
        $this->processService = $processService;
        $this->clusterService = $clusterService;
        $this->objectService = $objectService;
    }

    /**
     * Listen events
     */
    public function handle()
    {
        Redis::subscribe('clustering', function ($clusters) {
            $clusters = json_decode($clusters);

            if (!$clusters) {
                return;
            }
            $totalMongoIds = [];
            $clusterMongoIds = Arr::pluck($clusters, '_id');
            $existingClusters = $this->clusterService->listBy(function ($query) use ($clusterMongoIds) {
                return $query->whereIn('mongo_id', $clusterMongoIds);
            }, false);

            $this->objectService->updateMany('mongo_id', $this->getClusteringTypes($clusters));

            foreach ($clusters as &$cluster) {
                $existingCluster = Arr::first($existingClusters, function ($existingCluster) use ($cluster) {
                    return $existingCluster->mongo_id == $cluster->_id;
                });
                if (!$existingCluster) {
                    $identity = null;

                    if (object_get($cluster, 'identity')) {
                        $identity = $this->identityService->findBy(['mongo_id' => $cluster->identity]);
                    }
                    $cluster->id = $this->clusterService->create([
                        'identity_id' => $identity ? $identity->id : null,
                        'mongo_id' => $cluster->_id,
                    ]);
                } else {
                    $cluster->id = $existingCluster->id;
                }
                $objectMongoIds = Arr::pluck($cluster->objects, 'object');

                $this->objectService->updateBy(function ($query) use ($objectMongoIds) {
                    return $query->whereIn('mongo_id', $objectMongoIds);
                }, ['cluster_id' => $cluster->id]);

                $totalMongoIds = array_merge($totalMongoIds, $objectMongoIds);
            }
            $objects = $this->objectService->getFirstObjectsByMongoIds($totalMongoIds);
            $this->publishClusteringDataToEachProcess($objects);
        });
    }

    /**
     * @param $objects \Illuminate\Support\Collection
     */
    public function publishClusteringDataToEachProcess($objects)
    {
        $processesNewFormat = [];
        $processes = $objects->groupBy('process_id');

        foreach ($processes as $processId => $groupedObjects) {
            $process = $this->processService->getProcessDetail($processId);

            $processesNewFormat[] = $process;
            $groupedObjects = $this->objectService->assignAppearances($groupedObjects);

            // publish objects to process detail
            broadcast(new ClusteringProceeded([
                'statistic' => $process,
                'grouped_objects' => $groupedObjects,
            ], "process.$processId.cluster"));
        }
        // publish statistical numbers to monitoring page
        broadcast(new AnalysisProceeded($processesNewFormat));
    }

    public function getClusteringTypes($clusters)
    {
        $data = [];

        foreach ($clusters as $cluster) {
            foreach ($cluster->objects as $obj) {
                $data[] = [
                    'mongo_id' => $obj->object,
                    'clustering_type' => $obj->type
                ];
            }
        }
        return $data;
    }
}
