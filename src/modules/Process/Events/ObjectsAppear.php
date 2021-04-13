<?php

namespace Modules\Process\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use Modules\Process\Transformers\ObjectTransformer;

class ObjectsAppear implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $processId;
    private $objects;
    private $channel;
    private $fractal;

    /**
     * Create a new event instance.
     *
     * @param $processId
     * @param $data
     * @param string $channel
     */
    public function __construct($processId, $objects, $channel = 'monitor.tracking')
    {
        $this->fractal = app(Manager::class);
        $this->processId = $processId;
        $this->objects = $objects;
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channel);
    }

    public function broadcastWith()
    {
        $objects = new Collection($this->objects, new ObjectTransformer());
        $objects = $this->fractal->createData($objects); // Transform data
        $this->objects = $objects->toArray()['data'];

        return [
            'processId' => $this->processId,
            'data' => $this->objects,
        ];
    }
}
