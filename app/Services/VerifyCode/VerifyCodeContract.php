<?php namespace App\Services\VerifyCode;
/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 5:33 PM
 */

interface VerifyCodeContract
{
    /**
     * @param  string code sent type email or phone
     * @return int code
     */
    public function getVerifyCode($channel);

    /**
     *
     * @param $verifyCode
     * @return Eloquent new instance
     * @internal param Eloquent $code verifycode instance
     */
    public function saveVerifyCode($verifyCode);
    /**
     *
     * @param  intval $code code
     * @return bool
     */
    public function checkVerifyCode($verifyCode, $code);
}
