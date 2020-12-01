<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ObjectsAppear implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $processId;
    private $data;

    /**
     * Create a new event instance.
     *
     * @param $processId
     * @param $data
     */
    public function __construct($processId, $data)
    {
        $this->processId = $processId;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("process.$this->processId.objects");
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->data,
        ];
    }
}
