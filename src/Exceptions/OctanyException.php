<?php

namespace Octany\Exceptions;

use Exception;
use Throwable;

class OctanyException extends Exception
{
    protected $statusCode;

    protected $responseBody;

    public function __construct($message = '', $statusCode = 0, $responseBody = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function statusCode()
    {
        return $this->statusCode;
    }

    public function responseBody()
    {
        return $this->responseBody;
    }
}
