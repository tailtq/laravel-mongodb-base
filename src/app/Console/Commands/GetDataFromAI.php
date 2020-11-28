<?php

namespace App\Console\Commands;

use App\Events\ObjectsAppear;
use App\Helpers\DatabaseHelper;
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
use App\Traits\RequestAPI;

class GetDataFromAI extends Command
{
    use RequestAPI;

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
            Log::info($objects);
            $objects = json_decode($objects);

            if (!$objects) {
                return;
            }
            $insertingAppearances = [];
            $updatingAppearances = [];
            $updatingObjects = [];

            $processMongoIds = Arr::pluck($objects, 'process_id');
            $processes = DB::table('processes')->where('mongo_id', $processMongoIds)->get();

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
                if ($process->status === Process::STATUS['detecting']) {
                    DB::table('processes')
                        ->where('id', $process->id)
                        ->update([
                            'ungrouped_count' => DB::raw("(SELECT COUNT(*) FROM objects WHERE process_id = $process->id)"),
                        ]);
                }
            }
            $result = $this->queryAndBroadcastResult('objects.id', $objectIds);
            $this->runMatchingOnTrackedObjects($result);

            foreach ($processes as $process) {
                if ($process->status != Process::STATUS['detected']) {
                    continue;
                }

                $process = DB::table('processes')
                    ->where('id', $process->id)
                    ->select(['id', 'mongo_id', 'ungrouped_count'])
                    ->addSelect(DB::raw("(SELECT COUNT(*) FROM objects WHERE process_id = $process->id and matching_status = '" . TrackedObject::MATCHING_STATUS['identified'] . "') as identified_count"))
                    ->first();
                Log::info("Detected with ungrouped count: $process->ungrouped_count, identified count: $process->identified_count");

                if ($process->ungrouped_count == $process->identified_count) {
                    $this->groupData($process);
                }
            }
        });
    }

    // match identity handler
    public function runMatchingOnTrackedObjects($result)
    {
        $mappingIdentityIds = [];

        foreach ($result as $element) {
            if ($element->frame_to) {
                $mappingIdentityIds[] = $element->mongo_id;
            }
        }
        if (count($mappingIdentityIds) == 0) {
            return;
        }

        $response = $this->sendPOSTRequest(
            config('app.ai_server') . '/objects/matching',
            ['ids' => $mappingIdentityIds],
            $this->getDefaultHeaders()
        );

        if (!$response->status) {
            return;
        }
        $objectMongoIds = array_keys((array) $response->body);
        $identityMongoIds = array_values((array) $response->body);
        $updatingData = [];
        $matchedIdentityMongoIds = [];

        $identities = Identity::whereIn('mongo_id', $identityMongoIds)->select(['id', 'mongo_id'])->get();

        foreach ($objectMongoIds as $key => $mongoId) {
            $identityMongoId = $identityMongoIds[$key];

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
        $this->queryAndBroadcastResult('objects.mongo_id', $matchedIdentityMongoIds);
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

    // group data handler
    public function groupData($process)
    {
        $url = config('app.ai_server') . "/processes/$process->mongo_id/grouping2";
        $response = $this->sendPOSTRequest($url, [], $this->getDefaultHeaders());
        Log::info("Grouping $process->id response " . json_encode($response));

        if ($response->status) {
            $data = json_decode(json_encode($response->body), true);
            $this->updateObject($data);
        }
    }

    public static function updateObject($data)
    {
        // Response data AI
        $mongoIds = Arr::collapse(Arr::pluck($data, 'appearances.*.mongo_id'));
        $objects = TrackedObject::whereIn('mongo_id', $mongoIds)->select(['id', 'mongo_id'])->get();

        $identityMongoIds = array_filter(Arr::pluck($data, 'identity'), function ($element) {
            return $element != null;
        });
        $identities = Identity::whereIn('mongo_id', $identityMongoIds)->select(['id', 'mongo_id'])->get();
        $deleteIds = [];
        $identityData = [];

        foreach ($data as $element) {
            $appearanceMongoIds = Arr::pluck($element['appearances'], 'mongo_id');
            $object = $objects->where('mongo_id', $element['mongo_id'])->first();
            $identity = !empty($element['identity']) ? $identities->where('mongo_id', $element['identity'])->first() : null;

            if ($object) {
                $appearanceIds = $objects->whereIn('mongo_id', $appearanceMongoIds)
                    ->where('mongo_id', '!=', $object->mongo_id)
                    ->pluck('id')
                    ->all();
                ObjectAppearance::whereIn('object_id', $appearanceIds)->update(['object_id' => $object->id]);

                $identityData[] = [
                    'id' => $object->id,
                    'identity_id' => $identity->id ?? null
                ];
                $deleteIds = array_merge($deleteIds, $appearanceIds);
            }
        }
        TrackedObject::whereIn('id', $deleteIds)->delete();
        DatabaseHelper::updateMultiple($identityData, 'id', 'objects');
    }
}
