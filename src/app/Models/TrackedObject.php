<?php

namespace App\Models;

use Infrastructure\BaseModel;

class TrackedObject extends BaseModel
{
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

    protected $table = 'objects';

    public function appearances()
    {
        return $this->hasMany(ObjectAppearance::class, 'object_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }
}
