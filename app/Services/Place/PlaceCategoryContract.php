<?php

namespace App\Services\Place;

use App\Services\Core\EntityContract;


/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:10 PM
 */

interface PlaceCategoryContract extends EntityContract
{
    public function createPlaceCategory(array $placeCategory);
}