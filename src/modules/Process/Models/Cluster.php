<?php

namespace Modules\Process\Models;

use Infrastructure\BaseModel;

class Cluster extends BaseModel
{
    protected $fillable = [
        'identity_id',
        'mongo_id',
    ];

    protected $collection = 'clusters';
}
