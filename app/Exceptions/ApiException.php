<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException {
    private $_error;

    public function __construct(ApiError $error, Exception $innerException = null) {
        $this->_error = $error;
        parent::__construct($error->getStatusCode(), $error->getErrorMessage(), $innerException);
    }

    public function getError() {
        return $this->_error;
    }
}