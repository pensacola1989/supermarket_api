<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 12:26 AM
 */

namespace App\Services\MsgNotify;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 7:38 PM
 */

interface MsgNotifyContract extends EntityContract
{
    public function getNotifyCount($userId, $newPoint);
}
