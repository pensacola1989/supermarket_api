<?php

namespace App\Services\Place;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */
interface  PlaceBlockContract extends EntityContract
{
    public function createPlaceBlock($attribute);

    public function removeBlockUser($placeId, $userId);
}
