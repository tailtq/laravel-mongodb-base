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
    private $channel;

    /**
     * Create a new event instance.
     *
     * @param $processId
     * @param $data
     * @param string $channel
     */
    public function __construct($processId, $data, $channel = 'default')
    {
        $this->processId = $processId;
        $this->data = $data;
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        if ($this->channel === 'default') {
            return new Channel("process.$this->processId.objects");
        }
        return new Channel($this->channel);
    }

    public function broadcastWith()
    {
        return [
            'processId' => $this->processId,
            'data' => $this->data,
        ];
    }
}
