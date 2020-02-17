<?php

namespace App\Services\Place;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */
interface  PlaceApplyContract extends EntityContract
{
    public function createPlaceApply($attribute);

    public function getByExternalId($externalId);

    public function requireByExternalId($externalId);
}
