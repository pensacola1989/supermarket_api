<?php

namespace App\Services\Recommand;
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 11/3/17
 * Time: 3:03 PM
 */

use App\Services\Core\EntityContract;

interface RecommandContract extends EntityContract
{
    public function getRecomamndByOrder($order);
}