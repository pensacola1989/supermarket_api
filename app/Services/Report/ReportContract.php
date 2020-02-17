<?php

namespace App\Services\Report;

use App\Services\Core\EntityContract;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

interface ReportContract extends EntityContract {

    public function createReport($inputs);
}