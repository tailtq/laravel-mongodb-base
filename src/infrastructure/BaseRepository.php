<?php

namespace Infrastructure;

class BaseRepository
{
    protected $model;

    public function __construct(string $model)
    {
        $this->model = app($model);
    }
}
