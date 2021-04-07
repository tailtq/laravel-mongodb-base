<?php

namespace App\Models;

use Infrastructure\BaseModel;

class ObjectAppearance extends BaseModel
{
    protected $fillable = [
        'object_id',
        'frame_from',
        'frame_to',
        'time_from',
        'time_to',
    ];

    protected $table = 'object_appearances';
}
