<?php

namespace App\Services\Tag;

use App\Services\Core\EntityRepository;

class TagRepository extends EntityRepository
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        $query = $query->where('visible', 1);

        if (isset($criteria['placeId'])) {
            $query = $query->where('created_by_place_id', $criteria['placeId']);
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        return $query;
    }

    protected function loadRelated($entity)
    {
        $entity->load('place');
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }

    public function getTagsByPlaceId($placeId)
    {
        return $this->model->ofPlace($placeId)->ofVisible(1)->get();
    }

    public function placeHasTag($placeId, $tagName)
    {
        return $this->model->where('created_by_place_id', $placeId)->where('tag_name', $tagName)->count() > 0;
    }
}
