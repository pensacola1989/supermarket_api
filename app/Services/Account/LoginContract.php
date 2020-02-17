<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/18/17
 * Time: 5:01 PM
 */

namespace App\Services\Account;


use App\Services\Core\EntityContract;

interface LoginContract extends EntityContract
{
    public function getLoginByOpenId($openId);
}