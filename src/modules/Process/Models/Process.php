<?php

namespace Modules\Process\Models;

use Infrastructure\BaseModel;
use Modules\Camera\Models\Camera;

class Process extends BaseModel
{
    protected $collection = 'processes';

    const STATUS = [
        'ready' => 'ready',
        'stopped' => 'stopped',
        'detecting' => 'detecting',
        'detected' => 'detected',
        'clustering' => 'clustering',
        'done' => 'done',
        'error' => 'error',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'camera_id',
        'name',
        'video_url',
        'description',
        'status',
        'user_id',
        'thumbnail',
        'mongo_id',
        'total_time',
        'total_frames',
        'fps',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }
}
