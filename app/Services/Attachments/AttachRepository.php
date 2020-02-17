<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:59 PM
 */

namespace App\Services\Attachments;


use App\Services\Core\EntityContract;
use App\Services\Core\EntityRepository;
use App\Services\Post\Post;

class AttachRepository extends EntityRepository implements AttachContract
{

    public function __construct(Attachment $model)
    {
        $this->model = $model;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;
        if (isset($criteria['placeId'])) {
            $query = $query->whereHas('Post', function ($query) use ($criteria) {
                $query->whereIn('id', function ($query) use ($criteria) {
                    $query->select('id')
                        ->from(with(new Post)->getTable())
                        ->where('place_id', $criteria['placeId']);
                });
            });
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        $query->with(['Post']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        $entity->load(['Post']);
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
