<?php

namespace App\Console\Commands;

use App\Events\ObjectsAppear;
use App\Helpers\DatabaseHelper;
use App\Models\ObjectAppearance;
use App\Models\Process;
use App\Models\TrackedObject;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class GetDataFromAI extends Command
{
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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Redis::subscribe('process', function ($objects) {
            $objects = json_decode($objects);

            if (!$objects) {
                return;
            }
            $insertingAppearances = [];
//            $updatingAppearances = [];

            $processMongoIds = Arr::pluck($objects, 'process_id');
            $processes = Process::whereIn('mongo_id', $processMongoIds)->get();
            $objectIds = [];

//            $objectMongoIds = Arr::pluck($objects, 'mongo_id');
//            $trackedObjects = TrackedObject::whereIn('mongo_id', $objectMongoIds)->get();

            foreach ($objects as $object) {
//                if ($object->finished_track === false) {
                    $process = Arr::first($processes, function ($process) use ($object) {
                        return $object->process_id === $process->mongo_id;
                    });

                    if ($process) {
                        $trackedObject = TrackedObject::create([
                            'process_id' => $process->id,
                            'track_id' => $object->track_id,
                            'mongo_id' => $object->mongo_id,
                            'image' => $object->image,
                        ]);
                        array_push($insertingAppearances, [
                            'object_id' => $trackedObject->id,
                            'frame_from' => $object->frame_from,
                            'time_from' => object_get($object, 'time_from'),
                            // follow AI flow - Remove and uncomment the code below if change again
                            'frame_to' => object_get($object, 'frame_to'),
                            'time_to' => object_get($object, 'time_to'),
                            // end following AI flow
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                        $objectIds[] = $trackedObject->id;
                    }
//                } else {
//                    $trackedObject = Arr::first($trackedObjects, function ($trackedObject) use ($object) {
//                        return $trackedObject->mongo_id === $object->mongo_id;
//                    });
//
//                    if ($trackedObject) {
//                        array_push($updatingAppearances, [
//                            'object_id' => $trackedObject->id,
//                            'frame_to' => $object->frame_to,
//                            'time_to' => object_get($object, 'time_to'),
//                            'updated_at' => Carbon::now(),
//                        ]);
//                        $objectIds[] = $trackedObject->id;
//                    }
//                }
            }
            if (count($insertingAppearances) !== 0) {
                ObjectAppearance::insert($insertingAppearances);
            }
//            if (count($updatingAppearances) !== 0) {
//                DatabaseHelper::updateMultiple($updatingAppearances, 'object_id', 'object_appearances');
//            }
            $result = DB::table('objects')
                ->leftJoin('identities', 'objects.identity_id', 'identities.id')
                ->join('object_appearances', 'objects.id', 'object_appearances.object_id')
                ->whereIn('objects.id', $objectIds)
                ->select([
                    'objects.id',
                    'objects.process_id',
                    'objects.track_id',
                    'objects.image',
                    'identities.name',
                    'identities.images',
                    'object_appearances.frame_from',
                    'object_appearances.frame_to',
                    'object_appearances.time_from',
                    'object_appearances.time_to',
                    'objects.created_at',
                ])
                ->get();
            $groupedResult = $result->groupBy('process_id');
            $attrs = [];

            foreach ($groupedResult as $processId => $result) {
                $attrs[$processId] = $result;
            }

            Log::info($groupedResult);

            foreach ($attrs as $key => $value) {
                broadcast(new ObjectsAppear($key, $value));
            }
        });
    }
}
