<?php

namespace Modules\Camera\Commands;

use Infrastructure\Traits\RequestAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Modules\Camera\Events\NewEvent;

class ListenEvent extends Command
{
    use RequestAPI;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen event from redis';

    /**
     * Create a new command instance.
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
            broadcast(new NewEvent());
        });
    }
}
