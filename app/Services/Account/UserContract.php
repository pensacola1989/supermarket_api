<?php

namespace App\Services\Account;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 4:23 PM
 */
interface UserContract extends EntityContract
{
    public function getUserAll();

    public function createUser(array $userAttribute);

    public function updateUserInfo($id, $data);

    public function getUserByMobile($mobile);

    public function getUserSummary($userId);

    public function getNotifyCount($userId);

    public function clearNotify($userId);

    public function getOpenId($userId);
}
