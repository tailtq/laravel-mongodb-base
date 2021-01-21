<?php

namespace App\Console\Commands;

use App\Events\ProgressChange;
use App\Models\Process;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ListenProgress extends Command
{
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
            Log::info($event);
            $event = json_decode($event);

            if (!$event) {
                return;
            }
            $process = Process::where('mongo_id', $event->process_id)->first();

            if ($process) {
                $data = [
                    'id' => $process->id,
                    'status' => $event->status,
                    'progress' => $event->progress ?? 0,
                    'frame_index' => $event->frame_index ?? null,
                ];
//                if ($process->status === Process::STATUS['done'] && !$process->camera_id) {
//                    broadcast(new ProgressChange($process->id, $data));
//                }
                if ($event->status === 'rendered') {
                    $videoResult = object_get($event, 'video_url');
                    $process->video_result = $videoResult;
                    $process->save();

                    $data['video_result'] = $videoResult;
                }
                if ($process->status != $event->status && in_array($event->status, array_values(Process::STATUS))) {
                    $process->status = $event->status;
                    $process->save();
                }

                broadcast(new ProgressChange($process->id, $data));
            }
        });
    }
}
