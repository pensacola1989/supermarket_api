<?php

namespace App\Services\Place;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */

interface  PlaceSubApplyContract extends EntityContract
{
    public function approvePlaceSubApply($userId, $placeId);
}
