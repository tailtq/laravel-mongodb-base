<?php

namespace App\Console\Commands;

use App\Events\AnalysisProceeded;
use App\Events\ObjectsAppear;
use App\Helpers\DatabaseHelper;
use App\Traits\AnalysisTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ListenTrackingProcess extends Command
{
    use AnalysisTrait;

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

    /**
     * Map data from AI Server to Web Server
     */
    public function handle()
    {
        Redis::subscribe('process', function ($data) {
            $data = json_decode(json_decode($data));

            if (!$data) {
                return;
            }
            Log::info(json_encode($data));
            $process = DB::table('processes')
                ->where('mongo_id', $data->process_id)
                ->select(['id', 'mongo_id'])
                ->first();

            if (!$process) {
                return;
            }
            $objs = $data->objects_data;
            $objMongoIds = Arr::pluck($objs, '_id');
            $identityMongoIds = Arr::pluck($objs, 'identity');

            $identities = DB::table('identities')->whereIn('mongo_id', $identityMongoIds)->get();
            $existingObjs = DB::table('objects')
                ->whereIn('mongo_id', $objMongoIds)
                ->select(['id', 'mongo_id'])
                ->get();
            $updatingObjs = [];
            $reQueryObjIds = [];

            foreach ($objs as $obj) {
                # decide whether insert or update objects
                $existingObj = Arr::first($existingObjs, function ($existingObj) use ($obj) {
                    return $existingObj->mongo_id === $obj->_id;
                });
                # map identity from mongodb to mysql
                $identityId = object_get($obj, 'identity');

                if ($identityId) {
                    $identity = Arr::first($identities, function ($identity) use ($identityId) {
                        return $identity->mongo_id == $identityId;
                    });
                    $identityId = $identity ? $identity->id : null;
                }
                if ($existingObj) {
                    # update object
                    array_push($updatingObjs, [
                        'id' => $existingObj->id,
                        'identity_id' => $identityId,
                        'track_id' => $obj->track_id,
                        'images' => json_encode($obj->avatars),
                        'frame_from' => $obj->from_frame,
                        'frame_to' => object_get($obj, 'to_frame'),
                        'time_from' => object_get($obj, 'from_time'),
                        'time_to' => object_get($obj, 'to_time'),
                        'confidence_rate' => object_get($obj, 'confidence_rate'),
                        'updated_at' => Carbon::now(),
                    ]);
                    $reQueryObjIds[] = $existingObj->id;
                } else {
                    # insert new object
                    $reQueryObjIds[] = DB::table('objects')->insertGetId([
                        'process_id' => $process->id,
                        'identity_id' => $identityId,
                        'track_id' => $obj->track_id,
                        'mongo_id' => $obj->_id,
                        'images' => json_encode($obj->avatars),
                        'frame_from' => $obj->from_frame,
                        'frame_to' => object_get($obj, 'to_frame'),
                        'time_from' => object_get($obj, 'from_time'),
                        'time_to' => object_get($obj, 'to_time'),
                        'confidence_rate' => object_get($obj, 'confidence_rate'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            if (count($updatingObjs) !== 0) {
                # update many objects
                DatabaseHelper::updateMultiple($updatingObjs, 'id', 'objects');
            }
            $this->queryAndBroadcastResult($reQueryObjIds, $process->id);
        });
    }

    /**
     * Broadcast results after matching or tracking objects
     * @param $ids array
     * @param $processId int
     */
    public function queryAndBroadcastResult($ids, $processId)
    {
        # broadcast to detail page
        $objs = DB::table('objects')
            ->leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->whereIn('objects.id', $ids)
            ->select([
                'objects.*',
                'identities.name as identity_name',
                'identities.images as identity_images',
            ])
            ->get();
        broadcast(new ObjectsAppear($processId, $objs, "process.$processId.objects"));

        # broadcast to monitor page
        $process = DB::table('processes')->where('id', $processId)->select(
            array_merge(['id'], $this->getStatistic($processId))
        )->first();
        broadcast(new AnalysisProceeded([$process]));
    }
}
