<?php

namespace Modules\Process\Models;

use Infrastructure\BaseModel;

class TrackedObject extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'identity_id',
        'process_id',
        'track_id',
        'mongo_id',
        'image',
    ];

    const MATCHING_STATUS = [
        'ready' => 'ready',
        'identified' => 'identified',
    ];

    protected $collection = 'objects';

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }
}
