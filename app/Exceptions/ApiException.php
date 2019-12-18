<?php

class ApiException extends Exception {
    protected $statusCode = 500;

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
