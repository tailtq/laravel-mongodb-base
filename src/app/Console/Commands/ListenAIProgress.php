<?php

namespace App\Console\Commands;

use App\Events\ObjectVideoRendered;
use App\Events\ProgressChange;
use App\Models\Process;
use App\Models\TrackedObject;
use App\Traits\GroupDataTrait;
use App\Traits\RequestAPI;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ListenAIProgress extends Command
{
    use GroupDataTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-ai-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen status and progress event from AI';

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
     */
    public function handle()
    {
        Redis::subscribe('progress', function ($event) {
            Log::info($event);
            $event = json_decode($event);

            if (!$event) {
                return;
            }
            $process = Process::where('mongo_id', $event->process_id)->first();

            if ($process) {
                if (!empty($event->mongo_id)) {
                    if ($event->status === 'rendered') {
                        $this->getRenderingObjectEvent($event, $process);
                    }
                    return;
                }

                $data = [
                    'id' => $process->id,
                    'status' => $process->status,
                    'progress' => $event->progress ?? 0,
                    'frame_index' => $event->frame_index ?? null,
                ];
                if ($event->status === Process::STATUS['detected']) {
                    $process->detecting_end_time = Carbon::now();
                    $process->video_detecting_result = $event->video_url;
                }
                if ($event->status === Process::STATUS['grouped']) {
                    $process->rendering_start_time = Carbon::now();
                }
                if ($event->status === Process::STATUS['done']) {
                    $process->done_time = Carbon::now();
                    $process->video_result = $event->video_url;

                    $data['video_result'] = $process->video_result;
                    $data['video_detecting_result'] = $process->video_detecting_result;
                }
                if ($process->status != $event->status) {
                    $process->status = $event->status;
                    $process->save();

                    $data['status'] = $process->status;
                }
                // Call rendering API if status is grouped
                if ($event->status === Process::STATUS['grouped']) {
                    $url = config('app.ai_server') .  "/processes/$process->mongo_id/rendering";
                    $response = $this->sendGETRequest($url, [], $this->getDefaultHeaders());

                    Log::info("Rendering result $process->id: " . json_encode($response));
                }
                broadcast(new ProgressChange($process->id, $data));

                // Grouping in case GetDataFromAI event doesn't trigger grouping API
                if ($process->status === Process::STATUS['detected']) {
                    $this->callGroupingData([$process]);
                }
            }
        });
    }

    public function getRenderingObjectEvent($event, $process)
    {
        TrackedObject::where('mongo_id', $event->mongo_id)->update([
            'video_result' => $event->url
        ]);
        $object = TrackedObject::where('mongo_id', $event->mongo_id)->first();

        broadcast(new ObjectVideoRendered($process->id, $object));
    }
}
