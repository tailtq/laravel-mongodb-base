<?php

namespace App\Console\Commands;

use App\Events\ProgressChange;
use App\Models\Process;
use App\Traits\RequestAPI;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
            $event = json_decode($event);

            if (!$event) {
                return;
            }
            $process = Process::where('mongo_id', $event->process_id)->first();

            if ($process) {
                Log::info(json_encode($event));

                if ($event->status === 'rendered') {
                    $videoResult = object_get($event, 'video_url');
                    $process->video_result = $videoResult;
                    $process->save();

                    $data['video_result'] = $videoResult;
                }
                if ($process->status !== Process::STATUS['done']
                    && $process->status != $event->status
                    && in_array($event->status, array_values(Process::STATUS))) {
                    $process->status = $event->status;

                    if ($event->status === Process::STATUS['detected']) {
                        $process->detecting_end_time = Carbon::now();
                        $process->grouping_start_time = Carbon::now();
                    }
                    if ($event->status === Process::STATUS['done']) {
                        $process->done_time = Carbon::now();
                    }
                    $process->save();
                }

                $data = [
                    'id' => $process->id,
                    'status' => $process->status,
                    'progress' => $event->progress ?? 0,
                    'frame_index' => $event->frame_index ?? null,
                ];
                broadcast(new ProgressChange($process->id, $data));

                if ($process->status === Process::STATUS['detected']) {
                    Log::info("Called clustering");
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
    }
}
