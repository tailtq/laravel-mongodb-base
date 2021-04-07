<?php

namespace Infrastructure;

use Jenssegers\Mongodb\Eloquent\Model;

class BaseModel extends Model
{
    protected $primaryKey = '_id';
    protected $connection = 'mongodb';
}
