<?php namespace App\Services\VerifyCode;

use App;
use DB;
use Config;
use App\Services\VerifyCode\VerifyCodeContract;
use Carbon\Carbon;
use App\Services\Core\EntityRepository;
use App\Services\Facade\Helper;
/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 6:00 PM
 */
class VerifyCodeRepository extends EntityRepository implements VerifyCodeContract
{
    const EMAIL_PATTERN = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    const PHONE_PATTERN = "/1[3458]{1}\d{9}$/";
    /**
     * Constructor
     * @param Posts $model
     */
    public function __construct(VerifyCodes $model)
    {
        $this->model = $model;
    }

    /**
     * [getUserVerifyCode description]
     * @param  contact email or phone
     * @return VerifyCode
     */
    public function getVerifyCode($channel)
    {
        return $this->model->where('channel', $channel)->first();
    }
    /**
     * [saveUserVerifyCode both insert and update]
     * @param VerifyCodes Eloquent $code VerifyCodes instance
     * @return new code instance
     */
    public function saveVerifyCode($verifyCode)
    {
        return $this->save($verifyCode);
    }
    /**
     * check verifyCode valid
     * @param  Eloquent $code verifyCode Instance
     * @return bool
     */
    public function checkVerifyCode($verifyCode, $code)
    {
        if ($verifyCode->code !== $code) {
            return false;
        }
        $dt = Carbon::parse($verifyCode->updated_at);
        return $dt->diffInMinutes(Carbon::now(), true) < 15;
    }

    public function generateCode()
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $str .= rand(0, 9);
        }
        return $str;
    }
}
