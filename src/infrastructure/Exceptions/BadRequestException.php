<?php

namespace Infrastructure\Exceptions;

class BadRequestException extends BaseException
{
    public function __construct($message = 'BAD_REQUEST')
    {
        parent::__construct($message, 400);
    }
}
