<?php

namespace App\Console\Commands;

use App\Events\ProgressChange;
use App\Models\Process;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ListenAIProgress extends Command
{
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
                $data = [
                    'id' => $process->id,
                    'status' => $process->status,
                    'progress' => $event->progress ?? 0,
                ];
                if (!empty($event->stream_url)) {
                    $process->video_result = $event->stream_url;
                }
                if ($process->status != $event->status) {
                    $process->status = $event->status;
                    $process->save();

                    $data['status'] = $process->status;
                }
                broadcast(new ProgressChange($process->id, $data));
            }
        });
    }
}
