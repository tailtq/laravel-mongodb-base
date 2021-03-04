<?php

namespace Modules\Process\Commands;

use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Identity\Services\IdentityService;
use Modules\Process\Events\AnalysisProceeded;
use Modules\Process\Events\ObjectsAppear;
use App\Traits\AnalysisTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;

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

    protected $objectService;
    protected $processService;
    protected $identityService;

    /**
     * Create a new command instance.
     *
     * @param \Modules\Identity\Services\IdentityService $identityService
     * @param \Modules\Process\Services\ProcessService $processService
     * @param \Modules\Process\Services\ObjectService $objectService
     */
    public function __construct(IdentityService $identityService, ProcessService $processService, ObjectService $objectService)
    {
        parent::__construct();
        $this->identityService = $identityService;
        $this->objectService = $objectService;
        $this->processService = $processService;
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
            $process = $this->processService->findBy(['mongo_id' => $data->process_id]);

            if ($process instanceof ResourceNotFoundException) {
                return;
            }
            $objs = $data->objects_data;
            $objMongoIds = Arr::pluck($objs, '_id');
            $identityMongoIds = Arr::pluck($objs, 'identity');

            $identities = $this->identityService->listBy(function ($query) use ($identityMongoIds) {
                return $query->whereIn('mongo_id', $identityMongoIds);
            });
            $existingObjs = $this->objectService->listBy(function ($query) use ($objMongoIds) {
                return $query->where('mongo_id', $objMongoIds);
            });
            $updatingObjs = [];
            $creatingObjs = [];
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
                        'images' => json_encode($this->getFaceImages($obj)),
                        'body_images' => json_encode($this->getBodyImages($obj)),
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
                    $creatingObjs[] = [
                        'process_id' => $process->id,
                        'identity_id' => $identityId,
                        'track_id' => $obj->track_id,
                        'mongo_id' => $obj->_id,
                        'images' => json_encode($this->getFaceImages($obj)),
                        'body_images' => json_encode($this->getBodyImages($obj)),
                        'frame_from' => $obj->from_frame,
                        'frame_to' => object_get($obj, 'to_frame'),
                        'time_from' => object_get($obj, 'from_time'),
                        'time_to' => object_get($obj, 'to_time'),
                        'confidence_rate' => object_get($obj, 'confidence_rate'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }
            if (count($updatingObjs) !== 0) {
                $this->objectService->updateMany('id', $updatingObjs);
            }
            if (count($creatingObjs) !== 0) {
                $reQueryObjIds = array_merge(
                    $reQueryObjIds,
                    $this->objectService->create($data, true)
                );
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
        $objects = $this->objectService->getObjectsByIds($ids);
        broadcast(new ObjectsAppear($processId, $objects, "process.$processId.objects"));

        # broadcast to monitor page
        $process = $this->processService->getProcessDetail($processId);
        broadcast(new AnalysisProceeded([$process]));
        broadcast(new AnalysisProceeded($process, "process.$processId.analysis"));
    }

    /**
     * @param $obj
     * @return array
     */
    private function getFaceImages($obj)
    {
        $faces = [];

        foreach ($obj->face_ids ?? [] as $faceRange) {
            foreach ($faceRange as $face) {
                array_push($faces, $face->url);
            }
        }
        if (count($faces) == 0) {
            return $obj->avatars;
        }
        return $faces;
    }

    /**
     * @param $obj
     * @return array
     */
    private function getBodyImages($obj)
    {
        if (empty($obj->body_ids)) {
            return [];
        }
        return Arr::pluck($obj->body_ids, 'url');
    }
}
