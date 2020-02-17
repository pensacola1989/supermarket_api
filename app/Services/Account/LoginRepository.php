<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/18/17
 * Time: 5:02 PM
 */

namespace App\Services\Account;


use App\Services\Core\EntityBase;
use App\Services\Core\EntityContract;
use App\Services\Core\EntityRepository;

class LoginRepository extends EntityRepository implements LoginContract
{

    public function __construct(Login $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        return $query;
    }

    protected function includeForQuery($query)
    {
        return $query;
    }

    public function getLoginByOpenId($openId)
    {
        return $this->model->where('external_id', $openId)->where('external_system', 1)->first();
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }
}