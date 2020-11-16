<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackedObject extends Model
{
    protected $fillable = [
        'identity_id',
        'process_id',
        'track_id',
        'mongo_id',
    ];

    protected $table = 'objects';
}
