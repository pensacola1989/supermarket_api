<?php namespace App\Services\Exception;

class VerifyCodeExpireException extends VerifyCodeException {
    /**
    * @var integer
     */
    protected $statusCode = 401;
}
