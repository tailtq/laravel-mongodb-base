<?php

namespace Modules\Process\Commands;

use Infrastructure\Exceptions\ResourceNotFoundException;
use Modules\Process\Events\ObjectVideoRendered;
use Modules\Process\Events\ProgressChange;
use Modules\Process\Models\Process;
use App\Traits\RequestAPI;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Modules\Process\Services\ClusterService;
use Modules\Process\Services\ObjectService;
use Modules\Process\Services\ProcessService;

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
     * @var \Modules\Process\Services\ObjectService $objectService
     */
    protected $objectService;

    /**
     * @var \Modules\Process\Services\ProcessService $processService
     */
    protected $processService;

    /**
     * Create a new command instance.
     *
     * @param \Modules\Process\Services\ProcessService $processService
     * @param \Modules\Process\Services\ObjectService $objectService
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
            $process = $this->processService->findBy(['mongo_id' => $event->process_id]);

            if (!($process instanceof ResourceNotFoundException)) {
                Log::info(json_encode($event));

                if ($event->status === 'rendered' && !empty($event->mongo_id)) {
                    $this->getRenderingObjectEvent($event, $process);
                    return;
                }
                $updateData = [];

                if ($event->status === 'rendered') {
                    $videoResult = object_get($event, 'url');
                    $updateData['video_result'] = $videoResult;
                    $data['video_result'] = $videoResult;
                }
                if ($process->status !== Process::STATUS['done']
//                    && $process->status !== Process::STATUS['stopped']
                    && $process->status != $event->status
                    && in_array($event->status, array_values(Process::STATUS))) {
                    $updateData['status'] = $event->status;

                    if ($event->status === Process::STATUS['detected']) {
                        $updateData['detecting_end_time'] = Carbon::now();
                    }
                    if ($event->status === Process::STATUS['done']) {
                        $updateData['done_time'] = Carbon::now();
                    }
//                    if ($event->status === Process::STATUS['stopped']) {
//                        $updateData['detecting_end_time'] = Carbon::now();
//                        $updateData['done_time'] = Carbon::now();
//                    }
                }
                if (count($updateData) > 0) {
                    $this->processService->updateBy(['id' => $process->id], $updateData);
                    $process = $this->processService->findById($process->id);

                    $event->status = $process->status;
                }

                $data = [
                    'id' => $process->id,
                    'status' => $event->status,
                    'video_result' => $process->video_result,
                    'progress' => $event->progress ?? 0,
                    'frame_index' => $event->frame_index ?? null,
                ];
                broadcast(new ProgressChange($process->id, $data));

//                if ($process->status === Process::STATUS['detected'] || $process->status === Process::STATUS['stopped']) {
                if ($process->status === Process::STATUS['detected']) {
                    $this->runClustering($process->mongo_id);
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

        Log::info("Clustering: $url");
    }

    public function getRenderingObjectEvent($event, $process)
    {
        $this->objectService->updateBy(['mongo_id' => $event->mongo_id], [
            'video_result' => $event->url,
        ]);
        $object = $this->objectService->findBy(['mongo_id' => $event->mongo_id]);

        broadcast(new ObjectVideoRendered($process->id, $object));
    }
}
