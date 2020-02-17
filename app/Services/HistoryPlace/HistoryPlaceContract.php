<?php

namespace App\Services\HistoryPlace;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 7:38 PM
 */

interface HistoryPlaceContract extends EntityContract
{
    public function syncUserHistory(int $userId, int $placeId);

    public function getMyHistoryViews($userId, $isAdmin, $page = 1, $size = 6);
}
