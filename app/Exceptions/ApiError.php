<?php

namespace App\Exceptions;

use Exception;

class ApiError {
    private $_errorLevel;
    private $_code;
    private $_message;
    private $_status_code;

    public function __construct($errorLevel, $code, $message, $status_code) {
        $this->_errorLevel  = $errorLevel;
        $this->_code        = $code;
        $this->_message     = $message;
        $this->_status_code = $status_code;
    }

    public function getErrorCode() {
        return $this->_code;
    }

    public function getErrorMessage() {
        return $this->_message;
    }

    public function getStatusCode() {
        return $this->_status_code;
    }

    public function toException(Exception $innerException = null) {
        return new ApiException($this, $innerException);
    }
}