<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Redis::subscribe('process', function ($message) {
            // save objects to database
            // send to client via socket io
        });
        Redis::subscribe('analyze', function ($message) {
            // save analyzing status to database
            // send to client via socket io
        });
        Redis::subscribe('success', function ($message) {
            // save success status to database
            // send to client via socket io
        });
        return 0;
    }
}
