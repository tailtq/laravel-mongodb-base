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
        'image',
    ];

    protected $table = 'objects';

    public function appearances()
    {
        return $this->hasMany(ObjectAppearance::class, 'object_id');
    }
}
