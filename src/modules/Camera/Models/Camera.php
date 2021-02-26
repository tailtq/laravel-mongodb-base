<?php

namespace Modules\Camera\Models;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
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
