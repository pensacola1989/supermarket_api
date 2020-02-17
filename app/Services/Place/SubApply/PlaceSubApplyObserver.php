<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 3:22 PM
 */

namespace App\Services\Place\SubApply;

use MyHelper;
use App\Services\Place\PlaceContract;

class PlaceSubApplyObserver
{

    public function __construct()
    {
        $this->_placeRespository = app()->make(PlaceContract::class);
    }

    public function creating($model)
    { }

    public function created($model)
    { }

    public function updating($model)
    { }

    public function updated($model)
    {
        // after admin approve the subscription request, place_sub should add a record
        $this->_placeRespository->subscribePlace($model->user_id, $model->place_id);
    }

    public function saving($model)
    { }

    public function saved($model)
    { }

    public function deleting($model)
    { }

    public function deleted($model)
    { }
}
