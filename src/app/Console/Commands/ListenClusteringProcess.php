<?php

namespace App\Console\Commands;

use App\Events\AnalysisProceeded;
use App\Events\ClusteringProceeded;
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
            ->leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->whereIn('objects.id', function ($query) use ($objectMongoIds) {
                $query->select([DB::raw('MIN(id)')])
                    ->from('objects')
                    ->whereIn('mongo_id', $objectMongoIds)
                    ->groupBy(['cluster_id', 'process_id']);
            })
            ->select([
                'objects.*',
                'identities.name as identity_name',
                'identities.images as identity_images',
            ])
            ->get();

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

            array_push($processesNewFormat, $process);

            foreach ($groupedObjects as $object) {
                $object->appearances = DB::table('objects')
                    ->where('cluster_id', $object->cluster_id)
                    ->where('process_id', $object->process_id)
                    ->get();
            }
            broadcast(new ClusteringProceeded([
                'statistic' => $process,
                'grouped_objects' => $groupedObjects,
            ], "process.$processId.cluster"));
        }

        broadcast(new AnalysisProceeded($processes));
    }
}
