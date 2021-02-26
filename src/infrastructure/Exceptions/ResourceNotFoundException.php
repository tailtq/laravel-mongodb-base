<?php

namespace Infrastructure\Exceptions;

class ResourceNotFoundException extends BaseException
{
    public function __construct()
    {
        parent::__construct('RESOURCE_NOT_FOUND', 404);
    }
}
