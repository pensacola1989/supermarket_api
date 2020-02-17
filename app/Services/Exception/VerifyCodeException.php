<?php namespace App\Services\Exception;

use Exception;

class VerifyCodeException extends Exception {


    /**
     * @var integer
     */
    protected $statusCode = 500;
    /**
     * contructor
     * @param string $message    exception message
     * @param int $statusCode
     */
    public function __construct($message = 'An error occurred', $statusCode = null)
    {
        parent::__construct($message);

        if (! is_null($statusCode)) {
            $this->setStatusCode($statusCode);
        }
        $this->message = $message;
    }
    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    
}
