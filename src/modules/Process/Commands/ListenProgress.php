<?php

namespace Modules\Process\Commands;

use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Events\ObjectVideoRendered;
use Modules\Process\Events\ProgressChange;
use Modules\Process\Models\Process;
use App\Traits\RequestAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;
use MongoDB\BSON\ObjectId;

class ListenProgress extends Command
{
    use RequestAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen status and progress event from AI';

    /**
     * @var ObjectService $objectService
     */
    protected $objectService;

    /**
     * @var ProcessService $processService
     */
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
        $this->processService = $processService;
        $this->objectService = $objectService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Redis::subscribe('progress', function ($event) {
            $event = json_decode($event);

            if (!$event) {
                return;
            }
            $process = $this->processService->findBy(['_id' => new ObjectId($event->process_id)], 'error');

            if (!($process instanceof ResourceNotFoundException)) {
                if ($event->status === 'rendered' && !empty($event->mongo_id)) {
                    $this->getRenderingObjectEvent($event, $process);
                    return;
                }
                $updateData = [];

                if ($event->status === 'rendered') {
                    $videoResult = object_get($event, 'url');
                    $updateData['video_result'] = $videoResult;
                }
                if ($event->status === Process::STATUS['detecting'] && $event->progress > 0 && $event->progress != $process->progress) {
                    $updateData['detecting_progress'] = $event->progress;
                }
                if ($process->status !== Process::STATUS['done']
                    && $process->status !== Process::STATUS['stopped']
                    && $process->status != $event->status
                    && in_array($event->status, array_values(Process::STATUS))) {
                    $updateData['status'] = $event->status;

                    if ($event->status === Process::STATUS['detecting']) {
                        $updateData['detecting_start_time'] = dateNow();
                    }
                    if ($event->status === Process::STATUS['detected']) {
                        $updateData['detecting_end_time'] = dateNow();
                    }
                    if ($event->status === Process::STATUS['done']) {
                        $updateData['done_time'] = dateNow();
                    }
                    if ($event->status === Process::STATUS['stopped']) {
                        $updateData['detecting_end_time'] = dateNow();
                        $updateData['done_time'] = dateNow();
                    }
                }
                if (count($updateData) > 0) {
                    $this->processService->updateBy(['_id' => $process->_id], $updateData);
                    $process = $this->processService->findById($process->_id);
                }
                // just for publishing, not saving data
                if ($event->status === 'rendering' || $event->status === 'rendered') {
                    $process->status = $event->status;
                }
                $data = [
                    '_id' => $process->idString,
                    'status' => $process->status,
                    'progress' => $event->progress ?? 0,
                    'video_result' => $process->video_result,
                ];
                broadcast(new ProgressChange($process->idString, $data));

                if ($event->status === Process::STATUS['detected'] || $event->status === Process::STATUS['stopped']) {
                    $this->runClustering($process->idString);
                }
            }
        });
    }

    public function runClustering($processMongoId)
    {
        $url = config('app.ai_server') . '/clusters';
        $payload = [
            'search_body' => true,
            'process_ids' => [$processMongoId]
        ];
        $this->sendPOSTRequest($url, $payload, $this->getDefaultHeaders());

        Log::info("Clustering at the end ------------------");
    }

    public function getRenderingObjectEvent($event, $process)
    {
        $condition = ['_id' => new ObjectId($event->mongo_id)];

        $this->objectService->updateBy($condition, [
            'video_result' => $event->url,
        ]);
        $object = $this->objectService->findBy($condition);

        broadcast(new ObjectVideoRendered($process->idString, $object));
    }
}
