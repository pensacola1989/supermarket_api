<?php

namespace App\Services\Place;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */
interface  PlaceContract extends EntityContract
{
    public function createPlace($attribute);

    public function getByExternalId($externalId);

    public function requireByExternalId($externalId);

    public function isUserSubPlace($userId, $placeId);

    public function subscribePlace($userId, $placeId);

    public function userIsBlock($userId, $placeId);

    public function addPlaceSubApply($userId, $placeId);

    public function approvePlaceSubApply($userId, $placeId);

    public function userHasApplied($userId, $placeId);

    public function userNeedApply($userId, $placeId);

    public function unSubscribePlace($userId, $placeId);

    public function isUserAdmin($userId, $placeId);

    public function getPlaceDefaultConfigOptions();
}
