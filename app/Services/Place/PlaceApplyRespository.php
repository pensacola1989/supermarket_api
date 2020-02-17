<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityRepository;
use MyHelper;

class PlaceApplyRespository extends EntityRepository implements PlaceApplyContract
{
    private $categoryModel;

    private $attachmentModel;

    public function __construct(PlaceApply $model, PlaceCategories $categories, Attachment $attachment)
    {
        $this->model = $model;
        $this->categoryModel = $categories;
        $this->attachmentModel = $attachment;
    }

    public function createPlaceApply($attribute)
    {
        // $category = $this->categoryModel->find($attribute['category_id']);
        // $ret = $category->Places()->create($attribute);

        // return $this->getById($ret->id);
    }

    public function updatePlaceApply($externalId, $attribute)
    {
        // $category = $this->categoryModel->find($attribute['category_id']);
        // $place = $this->getByExternalId($externalId);
        // $place->update($attribute);
        // $place->Category()->associate($category);
        // $place->save();

        // return $this->getById($place->id);
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        return $query;
    }

    protected function includeForQuery($query)
    {
        // $query = $query->with(['Category', 'avatar']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        // $entity->load(['avatar', 'cover', 'Category']);
        // TODO: Implement loadRelated() method.
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
