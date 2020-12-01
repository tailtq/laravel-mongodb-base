<?php

namespace App\Console\Commands;

use App\Events\ObjectsAppear;
use App\Events\ProgressChange;
use App\Helpers\DatabaseHelper;
use App\Models\Identity;
use App\Models\ObjectAppearance;
use App\Models\Process;
use App\Models\TrackedObject;
use App\Traits\GroupDataTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class GetDataFromAI extends Command
{
    use GroupDataTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:get-ai-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from server AI';

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
     * Receive AI's data and map to MySQL database.
     * When finished_track = false, we insert data without MongoId (due to AI process).
     * Then when finished_track = true, based on track_id + process_id, we map frame_to and mongo_id to our data
     * Currently we handle multiple processes (create many + update many + publish many events) in the same time for optimization
     */
    public function handle()
    {
        Redis::subscribe('process', function ($objects) {
            $objects = json_decode($objects);

            if (!$objects) {
                return;
            }
            $insertingAppearances = [];
            $updatingAppearances = [];
            $updatingObjects = [];

            $processMongoIds = Arr::pluck($objects, 'process_id');
            $processes = DB::table('processes')->where('mongo_id', $processMongoIds)->get();

            if (count($processes) == 0) {
                return;
            }
            Log::info(json_encode($processes) . " jasldkfja skldfjasl kdfjklas djfl");

            // Get existing tracks for updating mongo_id and frame_to
            $trackIds = Arr::pluck($objects, 'track_id');
            $tracks = DB::table('objects')
                ->join('processes', 'processes.id', 'objects.process_id')
                ->whereIn('track_id', $trackIds)
                ->whereIn('processes.mongo_id', $processMongoIds)
                ->select(['objects.id', 'objects.mongo_id', 'processes.mongo_id as process_id'])
                ->get();
            $objectIds = [];

            foreach ($objects as $object) {
                // Object begins being tracked
                if ($object->finished_track === false) {
                    $process = Arr::first($processes, function ($process) use ($object) {
                        return $object->process_id === $process->mongo_id;
                    });

                    if ($process) {
                        $trackedObject = TrackedObject::create([
                            'process_id' => $process->id,
                            'track_id' => $object->track_id,
                            'image' => $object->image,
                        ]);
                        array_push($insertingAppearances, [
                            'old_object_id' => $trackedObject->id,
                            'object_id' => $trackedObject->id,
                            'frame_from' => $object->frame_from,
                            'time_from' => object_get($object, 'time_from'),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                        $objectIds[] = $trackedObject->id;
                    }
                } else { // Object finished being tracked
                    $track = Arr::first($tracks, function ($track) use ($object) {
                        return $track->process_id === $object->process_id;
                    });
                    if ($track) {
                        array_push($updatingObjects, [
                            'id' => $track->id,
                            'mongo_id' => $object->mongo_id,
                        ]);
                        array_push($updatingAppearances, [
                            'object_id' => $track->id,
                            'frame_to' => $object->frame_to,
                            'time_to' => object_get($object, 'time_to'),
                            'updated_at' => Carbon::now(),
                        ]);
                        $objectIds[] = $track->id;
                    }
                }
            }
            if (count($insertingAppearances) !== 0) {
                ObjectAppearance::insert($insertingAppearances);
            }
            if (count($updatingAppearances) !== 0) {
                DatabaseHelper::updateMultiple($updatingObjects, 'id', 'objects');
                DatabaseHelper::updateMultiple($updatingAppearances, 'object_id', 'object_appearances');
            }
            foreach ($processes as $process) {
                if (in_array($process->status, [Process::STATUS['detecting'], Process::STATUS['detected']])) {
                    DB::table('processes')
                        ->where('id', $process->id)
                        ->update([
                            'ungrouped_count' => DB::raw("(SELECT COUNT(*) FROM objects WHERE process_id = $process->id)"),
                        ]);
                }
            }
            $result = $this->queryAndBroadcastResult('objects.id', $objectIds);
            $this->runMatchingOnTrackedObjects($result);
            $this->callGroupingData($processes);
        });
    }

    // match identity handler
    public function runMatchingOnTrackedObjects($result)
    {
        $mappingIdentityIds = [];
        $processIds = [];

        foreach ($result as $element) {
            if (is_int($element->frame_to) && $element->frame_to >= 0) {
                $mappingIdentityIds[] = $element->mongo_id;
                $processIds[] = $element->process_id;
            }
        }
        Log::info(json_encode($mappingIdentityIds));
        if (count($mappingIdentityIds) == 0) {
            return;
        }

        $response = $this->sendPOSTRequest(
            config('app.ai_server') . '/objects/matching',
            ['ids' => $mappingIdentityIds],
            $this->getDefaultHeaders()
        );
        Log::info(config('app.ai_server') . '/objects/matching' . "   " . json_encode($response));

        if (!$response->status) {
            return;
        }
        $body = (array) $response->body;
        $identityMongoIds = array_values((array) $response->body);
        $updatingData = [];
        $matchedIdentityMongoIds = [];

        $identities = Identity::whereIn('mongo_id', $identityMongoIds)->select(['id', 'mongo_id'])->get();

        foreach ($mappingIdentityIds as $key => $mongoId) {
            $identityMongoId = $body[$mongoId] ?? null;

            $identity = $identities->first(function ($value) use ($identityMongoId) {
                return $value->mongo_id == $identityMongoId;
            });
            $updatingData[] = [
                'mongo_id' => $mongoId,
                'identity_id' => $identity->id ?? null,
                'matching_status' => TrackedObject::MATCHING_STATUS['identified'],
            ];

            if ($identity) {
                $matchedIdentityMongoIds[] = $mongoId;
            }
        }
        DatabaseHelper::updateMultiple($updatingData, 'mongo_id', 'objects');

        if (count($matchedIdentityMongoIds) > 0) {
            $this->queryAndBroadcastResult('objects.mongo_id', $matchedIdentityMongoIds);
        }
        foreach ($processIds as $processId) {
            broadcast(new ProgressChange($processId, [
                'id' => $processId,
                'status' => 'matching',
                'total' => DB::table('objects')->where([
                    'process_id' => $processId,
                    'matching_status' => TrackedObject::MATCHING_STATUS['identified']
                ])->count(),
            ]));
        }
    }

    public function queryAndBroadcastResult($whereInColumn, $whereInValue)
    {
        $result = DB::table('objects')
            ->leftJoin('identities', 'objects.identity_id', 'identities.id')
            ->join('object_appearances', 'objects.id', 'object_appearances.object_id')
            ->whereIn($whereInColumn, $whereInValue)
            ->select([
                'objects.id',
                'objects.process_id',
                'objects.track_id',
                'objects.mongo_id',
                'objects.image',
                'identities.name',
                'identities.images',
                'object_appearances.frame_from',
                'object_appearances.frame_to',
            ])
            ->get();
        $groupedResult = $result->groupBy('process_id');
        $attrs = [];

        foreach ($groupedResult as $processId => $result) {
            $attrs[$processId] = $result;
        }
        foreach ($attrs as $key => $value) {
            broadcast(new ObjectsAppear($key, $value));
        }

        return $result;
    }
}
