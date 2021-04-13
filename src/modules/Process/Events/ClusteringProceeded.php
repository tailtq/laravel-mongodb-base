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
use Modules\Process\Transformers\ObjectTransformer;

class ClusteringProceeded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $processId;
    private $data;
    private $channel;
    private $fractal;

    /**
     * Create a new event instance.
     *
     * @param $data
     * @param string $channel
     */
    public function __construct($process, $groupedObjects, $channel = 'monitor.clustering')
    {
        $this->fractal = app(Manager::class);
        $this->channel = $channel;
        $this->data = [
            'process' => $process,
            'grouped_objects' => $groupedObjects
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel($this->channel);
    }

    public function broadcastWith()
    {
        $objects = new Collection($this->data['grouped_objects'], new ObjectTransformer());
        $objects = $this->fractal->createData($objects); // Transform data
        $this->data['grouped_objects'] = $objects->toArray()['data'];

        return [
            'data' => $this->data,
        ];
    }
}
