<?php

namespace App\Console\Commands;

use App\Helpers\DatabaseHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ListenTrackingProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-tracking-process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen tracking process from AI server';

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
        Redis::subscribe('tracking', function ($objs) {
            Log::info('Tracking ============ ' . $objs);
            $objs = json_decode($objs);

            if (!$objs) {
                return;
            }
            $objMongoIds = Arr::pluck($objs, '_id');
            $processMongoIds = Arr::pluck($objs, 'process');
            $identityMongoIds = Arr::pluck($objs, 'identity');

            $process = DB::table('processes')
                ->where('mongo_id', $processMongoIds)
                ->select(['id', 'mongo_id'])
                ->first();

            if (!$process) {
                return;
            }
            $identities = DB::table('identities')->whereIn('mongo_id', $identityMongoIds)->get();
            $existingObjs = DB::table('objects')
                ->whereIn('track_id', $objMongoIds)
                ->select(['id', 'mongo_id'])
                ->get();
            $reQueryObjIds = [];
            $insertingObjs = [];
            $updatingObjs = [];

            foreach ($objs as $obj) {
                $existingObj = Arr::first($existingObjs, function ($existingObj) use ($obj) {
                    return $existingObj->mongo_id === $obj->_id;
                });
                $identityId = object_get($obj, 'identity');

                if ($identityId) {
                    $identity = Arr::first($identities, function ($identity) use ($identityId) {
                        return $identity->mongo_id == $identityId;
                    });
                    $identityId = $identity ? $identity->id : null;
                }
                if ($existingObj) {
                    array_push($updatingObjs, [
                        'id' => $existingObj->id,
                        'identity_id' => $identityId,
                        'track_id' => $obj->track_id,
                        'images' => json_encode($obj->images),
                        'frame_from' => $obj->frame_from,
                        'frame_to' => object_get($obj, 'frame_to'),
                        'time_from' => object_get($obj, 'time_from'),
                        'time_to' => object_get($obj, 'time_to'),
                        'confidence_rate' => object_get($obj, 'confidence_rate'),
                        'updated_at' => Carbon::now(),
                    ]);
                    $reQueryObjIds[] = $existingObj->id;
                } else {
                    array_push($insertingObjs, [
                        'identity_id' => $identityId,
                        'track_id' => $obj->track_id,
                        'images' => json_encode($obj->images),
                        'frame_from' => $obj->frame_from,
                        'frame_to' => object_get($obj, 'frame_to'),
                        'time_from' => object_get($obj, 'time_from'),
                        'time_to' => object_get($obj, 'time_to'),
                        'confidence_rate' => object_get($obj, 'confidence_rate'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            if (count($insertingObjs) !== 0) {
                foreach ($insertingObjs as $insertingObj) {
                    $reQueryObjIds[] = DB::table('objects')->insertGetId($insertingObj);
                }
            }
            if (count($updatingObjs) !== 0) {
                DatabaseHelper::updateMultiple($updatingObjs, 'id', 'objects');
            }
            # publish output
//            $result = $this->queryAndBroadcastResult('objects.id', $objectIds);
        });
    }
}
