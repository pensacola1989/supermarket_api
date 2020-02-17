<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 3:22 PM
 */

namespace App\Services\Place;

use MyHelper;

class PlaceObserver
{
    public function creating($model)
    {
        $snowId = MyHelper::newId();
        $model->external_id = $snowId;

        $gpsStr = $model->lat . ',' . $model->lng;
        $model->geo_hash = MyHelper::convertGeoToHash($gpsStr);

    }
}