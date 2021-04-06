<?php

namespace Modules\Camera\Models;

use Infrastructure\BaseModel;

class Camera extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'url', 'mongo_id'
    ];
}
