<?php

namespace Infrastructure;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class BaseModel extends Model
{
    protected $primaryKey = '_id';
    protected $connection = 'mongodb';

    protected $appends = ['idString'];

    /**
     * @param null $value
     * @return mixed|string|null
     */
    public function getIdAttribute($value = null): ObjectId
    {
        // If we don't have a value for 'id', we will use the Mongo '_id' value.
        // This allows us to work with models in a more sql-like way.
        if (!$value && array_key_exists('_id', $this->attributes)) {
            $value = $this->attributes['_id'];
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getIdStringAttribute(): string
    {
        return (string) $this->id;
    }

}
