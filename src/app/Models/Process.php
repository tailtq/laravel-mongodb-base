<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    const STATUS = [
        'ready' => 'ready',
        'running' => 'running',
        'paused' => 'paused',
        'stopped' => 'stopped'
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
    ];
}
