<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    const STATUS = [
        'ready' => 'ready',
        'stopped' => 'stopped',
        'detecting' => 'detecting',
        'grouping' => 'grouping',
        'done' => 'done',
        'error' => 'error',
        'rendering' => 'rendering',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
}
