<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:50 AM
 */

namespace App\Services\Report;


use App\Services\Account\User;
use App\Services\Core\EntityRepository;
use App\Services\Post\Post;

class ReportRepository extends EntityRepository implements ReportContract
{
    private $_userContext;

    private $_postContext;

    public function __construct(Report $model, User $userContext, Post $postContext)
    {
        $this->model = $model;
        $this->_userContext = $userContext;
        $this->_postContext = $postContext;
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

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    public function createReport($inputs)
    {
        $user = $this->_userContext->findOrFail($inputs['report_from_uid']);
        $post = $this->_postContext->findOrFail($inputs['report_post_id']);

        $report = $this->getNew([]);

        $report->User()->associate($user);
        $report->Post()->associate($post);

        $report->save();

        return $report;
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
