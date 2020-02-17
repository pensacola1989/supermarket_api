<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/18/17
 * Time: 5:01 PM
 */

namespace App\Services\Account\Block;


use App\Services\Core\EntityContract;

interface UserBlockContract extends EntityContract
{
    public function userIsBlockByPoster($posterId, $userId);

    public function getOneBlockUser($userId, $blockUserId);
}
