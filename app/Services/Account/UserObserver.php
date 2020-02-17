<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 3:22 PM
 */

namespace App\Services\Account;

use MyHelper;

class UserObserver
{
    public function creating($model)
    {
        $snowId = MyHelper::newId();
        $model->external_id = $snowId;
    }

    /**
     * when a comment created ,update the comment number of post table
     * @param  Eloquent $model new Comment instance
     * @return void
     */
    private function inrementsPostCommentNum($model)
    {

    }

}