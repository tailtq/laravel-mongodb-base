<?php

namespace Modules\Process\Commands;

use Illuminate\Support\Facades\Log;
use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Events\AnalysisProceeded;
use Modules\Process\Events\ObjectsAppear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;
use MongoDB\BSON\ObjectId;

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

    protected $objectService;
    protected $processService;

    /**
     * Create a new command instance.
     *
     * @param ProcessService $processService
     * @param ObjectService $objectService
     */
    public function __construct(ProcessService $processService, ObjectService $objectService)
    {
        parent::__construct();
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
            $process = $this->processService->findBy(['_id' => new ObjectId($data->process_id)], 'error');

            if ($process instanceof ResourceNotFoundException) {
                return;
            }
            $ids = array_map(function ($object) {
                return new ObjectId($object->_id);
            }, $data->objects_data);

            $this->queryAndBroadcastResult($ids, $process->_id);
        });
    }

    /**
     * Broadcast results after matching or tracking objects
     * @param $ids array
     * @param ObjectId $processId int
     */
    public function queryAndBroadcastResult($ids, ObjectId $processId)
    {
        $processIdString = (string) $processId;
        # broadcast to detail page
        $objects = $this->objectService->listFirstObjectsByIds($ids);
        broadcast(new ObjectsAppear($processIdString, $objects, "process.$processIdString.objects"));

        # broadcast to monitor page
        $process = $this->processService->getProcessDetail($processId);
        broadcast(new AnalysisProceeded($process->toArray(), "process.$processIdString.analysis"));
        broadcast(new AnalysisProceeded([$process->toArray()]));
    }
}
