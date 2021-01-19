<?php

namespace App\Console\Commands;

use App\Events\ClusteringProceeded;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
            Log::info('Clustering ============ ' . $clusters);
            $clusters = json_decode($clusters);
            # [{"identity", "_id", "objects"}]

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
            $objectsWithAppearances = $this->publishClusteringDataToMonitorPage($objects);
            $this->publishClusteringDataToEachProcess($objectsWithAppearances);
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
                    ->groupBy('cluster_id');
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
     * @param $objectsWithAppearances \Illuminate\Support\Collection
     */
    public function publishClusteringDataToEachProcess($objectsWithAppearances)
    {
        $processes = $objectsWithAppearances->groupBy('process_id');

        foreach ($processes as $process => $objects) {
            foreach ($objects as $object) {
                $object->appearances = $object->appearances->filter(function ($appearance) use ($process) {
                    return $appearance->process_id == $process;
                });
            }
            broadcast(new ClusteringProceeded($objects, "process.$process.cluster"));
        }
    }

    /**
     * @param $objects
     * @return \Illuminate\Support\Collection
     */
    public function publishClusteringDataToMonitorPage($objects)
    {
        foreach ($objects as $object) {
            $object->appearances = DB::table('objects')
                ->where('id', '!=', $object->id)
                ->where('cluster_id', $object->cluster_id)
                ->get();
        }
        broadcast(new ClusteringProceeded($objects));

        return $objects;
    }
}
