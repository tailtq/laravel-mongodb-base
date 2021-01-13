<?php

namespace App\Console\Commands;

use App\Helpers\DatabaseHelper;
use App\Models\Cluster;
use App\Models\Identity;
use App\Models\ObjectAppearance;
use App\Models\Process;
use App\Models\TrackedObject;
use Carbon\Carbon;
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

    public function handle()
    {
        Redis::subscribe('clustering', function ($clusters) {
            Log::info('Clustering ============ ' . $clusters);
            $clusters = json_decode($clusters);
            # [{"identity", "_id", "objects"}]

            if (!$clusters) {
                return;
            }
            $clusterMongoIds = Arr::pluck($clusters, '_id');
            $existingClusters = Cluster::where('mongo_id', $clusterMongoIds)
                ->select(['id', 'identity_id'])
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
                    $cluster->id = DB::table('clusters')->insert([
                        'identity_id' => $identity ? $identity->id : null,
                        'mongo_id' => $cluster->_id,
                    ]);
                } else {
                    $cluster->id = $existingCluster->id;
                }
                DB::table('objects')
                    ->whereIn('mongo_id', $cluster->objects)
                    ->update(['cluster_id' => $cluster->id]);
            }
            # publish event
        });
    }
}
