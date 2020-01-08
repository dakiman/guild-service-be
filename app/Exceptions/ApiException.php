<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception {
    protected int $statusCode;

    public function __construct($message = "Something went wrong...", $statusCode = 500)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, 0, null);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
