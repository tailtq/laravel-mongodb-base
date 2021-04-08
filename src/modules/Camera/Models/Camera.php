<?php

namespace Modules\Camera\Models;

use Infrastructure\BaseModel;
use Modules\Process\Models\Process;

class Camera extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'url'
    ];

    public function processRelations()
    {
        return $this->hasMany(Process::class, 'camera', '_id');
    }
}
