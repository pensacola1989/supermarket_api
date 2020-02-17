<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/18/17
 * Time: 5:02 PM
 */

namespace App\Services\Account\Block;


use App\Services\Core\EntityBase;
use App\Services\Core\EntityContract;
use App\Services\Core\EntityRepository;

class UserBlockRepository extends EntityRepository implements UserBlockContract
{

    public function __construct(UserBlock $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['userId'])) {
            $query = $query->where('user_id', $criteria['userId']);
        }
        return $query;
    }

    protected function includeForQuery($query)
    {
        return $query;
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }

    public function userIsBlockByPoster($posterId, $userId)
    {
        return $this->model
            ->where([
                ['user_id', '=', $posterId],
                ['block_user_id', '=', $userId]
            ])
            ->count() > 0;
    }

    public function getOneBlockUser($userId, $blockUserId)
    {
        return $this->model
            ->where([
                ['user_id', '=', $userId],
                ['block_user_id', '=', $blockUserId]
            ])
            ->first();
    }
}
