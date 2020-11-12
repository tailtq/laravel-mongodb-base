<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    public const STATUS = [
        'tracking' => 'tracking',
        'untracking' => 'untracking'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'images', 'status', 'info', 'mongo_id', 'card_number'
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
