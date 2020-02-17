<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 10:07 AM
 */

namespace App\Services\Exception;


use Exception;

class NotAllowException extends Exception
{


    /**
     * @var integer
     */
    private $_statusCode = 102;

    /**
     * contructor
     * @param string $message exception message
     * @param int $statusCode
     */
    public function __construct($message = 'not allowed', $statusCode = null)
    {
        parent::__construct($message);

        if (!is_null($statusCode)) {
            $this->setStatusCode($statusCode);
        }
        $this->message = $message;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->_statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

}
