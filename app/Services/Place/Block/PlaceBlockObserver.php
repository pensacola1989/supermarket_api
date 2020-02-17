<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 3:22 PM
 */

namespace App\Services\Place\Block;

use MyHelper;
use App\Services\Place\PlaceContract;
use App\Services\HistoryPlace\HistoryPlaceContract;

class PlaceBlockObserver
{
    private $_placeRepository;

    private $_historyRespository;

    public function __construct()
    {
        $this->_placeRepository = app()->make(PlaceContract::class);
        $this->_historyRespository = app()->make(HistoryPlaceContract::class);
    }

    public function creating($model)
    { }

    public function created($model)
    {
        // remove subscription
        $this->_placeRepository->unSubscribePlace($model->user_id, $model->place_id);
        // 移除浏览记录
        $history = $this->_historyRespository
            ->getModel()
            ->where([
                'user_id' => $model->user_id,
                'place_id' => $model->place_id
            ])
            ->first();

        $history && $this->_historyRespository->delete($history);
    }

    public function updating($model)
    { }

    public function updated($model)
    { }

    public function saving($model)
    { }

    public function saved($model)
    { }

    public function deleting($model)
    { }
}
