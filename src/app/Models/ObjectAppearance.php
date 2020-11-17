<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectAppearance extends Model
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
