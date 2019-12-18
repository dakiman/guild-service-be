<?php

class ApiException extends Exception {
    protected int $statusCode = 500;
    protected $message = "Something went wrong...";

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
