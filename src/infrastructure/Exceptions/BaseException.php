<?php

namespace Infrastructure\Exceptions;

abstract class BaseException extends \Error
{
    /**
     * @var \stdClass $data
     */
    protected $data;

    public function __construct(string $message, int $code, \stdClass $data = null)
    {
        parent::__construct($message, $code, null);
        $this->data = $data;
    }

    /**
     * @return \stdClass
     */
    public function getData(): \stdClass
    {
        return $this->data;
    }
}
