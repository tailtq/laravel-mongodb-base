<?php

namespace App\Console\Commands;

use App\Events\AnalysisProceeded;
use App\Events\ClusteringProceeded;
use App\Helpers\DatabaseHelper;
use App\Traits\AnalysisTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
            $existingClusters = DB::table('clusters')
                ->whereIn('mongo_id', $clusterMongoIds)
                ->select(['id', 'identity_id', 'mongo_id'])
                ->get();
            DatabaseHelper::updateMultiple($this->getClusteringTypes($clusters), 'mongo_id', 'objects');

            foreach ($clusters as &$cluster) {
                $existingCluster = Arr::first($existingClusters, function ($existingCluster) use ($cluster) {
                    return $existingCluster->mongo_id == $cluster->_id;
                });

                if (!$existingCluster) {
                    $identity = null;

                    if (object_get($cluster, 'identity')) {
                        $identity = DB::table('identities')
                            ->where('mongo_id', $cluster->identity)
                            ->select(['id'])
                            ->first();
                    }
                    $cluster->id = DB::table('clusters')->insertGetId([
                        'identity_id' => $identity ? $identity->id : null,
                        'mongo_id' => $cluster->_id,
                    ]);
                } else {
                    $cluster->id = $existingCluster->id;
                }
                $objectMongoIds = array_map(function ($object) {
                    return $object->object;
                }, $cluster->objects);

                DB::table('objects')
                    ->whereIn('mongo_id', $objectMongoIds)
                    ->update(['cluster_id' => $cluster->id]);

                $totalMongoIds = array_merge($totalMongoIds, $objectMongoIds);
            }

            $objects = $this->getClusteredObjects($totalMongoIds);
            $this->publishClusteringDataToEachProcess($objects);
        });
    }

    /**
     * @param $objectMongoIds array[string]
     * @return \Illuminate\Support\Collection
     */
    public function getClusteredObjects($objectMongoIds)
    {
        $objects = DB::table('objects')
            ->leftJoin('clusters', 'objects.cluster_id', 'clusters.id')
            ->leftJoin('identities as CI', 'clusters.identity_id', 'CI.id')
            ->leftJoin('identities as OI', 'objects.identity_id', 'OI.id')
            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
                $query->select([DB::raw('MIN(id)')])
                    ->from('objects')
                    ->whereIn('mongo_id', $objectMongoIds)
                    ->groupBy(['cluster_id', 'process_id']);
            })
            ->select([
                'objects.*',
                'OI.name as identity_name',
                'OI.images as identity_images',
                'CI.name as cluster_identity_name',
                'CI.images as cluster_identity_images',
            ])
            ->get();
        $objects = DatabaseHelper::blendObjectsIdentity($objects);

        return $objects;
    }

    /**
     * @param $objects \Illuminate\Support\Collection
     */
    public function publishClusteringDataToEachProcess($objects)
    {
        $processesNewFormat = [];
        $processes = $objects->groupBy('process_id');

        foreach ($processes as $processId => $groupedObjects) {
            $process = DB::table('processes')->where('id', $processId)->select(
                array_merge(['id'], $this->getStatistic($processId))
            )->first();

            $processesNewFormat[] = $process;
            $groupedObjects = $this->getAppearances($groupedObjects);

            broadcast(new ClusteringProceeded([
                'statistic' => $process,
                'grouped_objects' => $groupedObjects,
            ], "process.$processId.cluster"));
        }

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
