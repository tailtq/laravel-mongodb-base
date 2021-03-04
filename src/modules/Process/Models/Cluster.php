<?php

namespace Modules\Process\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = [
        'identity_id',
        'mongo_id',
    ];

    protected $table = 'clusters';
}
