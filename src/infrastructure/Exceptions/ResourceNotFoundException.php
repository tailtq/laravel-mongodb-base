<?php

namespace Infrastructure\Exceptions;

class ResourceNotFoundException extends BaseException
{
    public function __construct($message = 'RESOURCE_NOT_FOUND')
    {
        parent::__construct($message, 404);
    }
}
