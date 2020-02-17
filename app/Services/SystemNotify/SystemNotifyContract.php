<?php

namespace App\Services\SystemNotify;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

interface SystemNotifyContract extends EntityContract
{
    public function getUserUnreadSystemNotifyNum($userLastReadTime);
}
