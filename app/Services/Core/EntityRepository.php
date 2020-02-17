<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 12:23 AM
 */

namespace App\Services\Core;

use App\Services\Exception;
use App\Services\Exception\EntityNotFoundException;

abstract class EntityRepository implements EntityContract
{

    protected $model;

    abstract protected function constructQuery($criteria);

    abstract protected function includeForQuery($query);

    abstract protected function loadRelated($entity);


    //
    //    protected abstract function attachRelated($entity, $relatedIds);
    //
    //    protected abstract function updateRelated($entity, $relatedIds);

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    /**
     * get a model collection by paginate
     * @param $count
     * @return mixed
     */
    public function getAllPaginated($count)
    {
        return $this->model->paginate($count);
    }

    /**
     * get a model by primary key
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        $query = $this->includeForQuery($this->model);

        return $query->find($id);
    }

    /**
     * get a model if not exist throw a Exception
     * @param $id
     * @return mixed
     * @throws Exception\EntityNotFoundException
     */
    public function requireById($id)
    {
        $model = $this->getById($id);
        if (!$model) {
            throw new Exception\EntityNotFoundException;
        }
        $this->loadRelated($model);
        return $model;
    }

    /**
     * get model
     * @return null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * set model
     * @param $model
     * @return mixed|void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * get all models
     * @return mixed
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * get a new model by a specific array
     * @param array $attributes
     * @return mixed
     */
    public function getNew($attributes = array())
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * save a model
     * @param $data
     * @return bool
     */
    public function save($data)
    {
        if ($data instanceof EntityBase) {
            return $this->storeEloquentModel($data);
        } elseif (is_array($data)) {
            return $this->storeArray($data);
        }
    }

    /**
     * delete a specific model
     * @param $model
     * @return mixed
     */
    public function delete($model)
    {
        return $model->delete();
    }

    /**
     * store by Eloquent model
     * @param $model
     * @return boolean
     */
    public function storeEloquentModel($model)
    {
        if ($model->getDirty()) {
            return $model->save();
        } else {
            return $model->touch();
        }
    }

    /**
     * store by array
     * @param $data
     * @return bool
     */
    public function storeArray($data)
    {
        $model = $this->getNew($data);
        return $this->storeEloquentModel($model);
    }

    /**
     * return a new object
     * @param $data
     * @return mixed
     */
    public function createModel($data)
    {
        return $this->model->create($data);
    }

    public function updateModel($id, $data)
    {
        $model = $this->getByExternalId($id);
        $model->update($data);

        return $this->requireByExternalId($id);
        //        return $model->update($data);
    }

    public function getByExternalId($externalId)
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    /**
     * override base requireById to by uid
     * @param $externalId
     * @return mixed
     * @throws EntityNotFoundException
     * @internal param bigInt $uid
     */
    public function requireByExternalId($externalId)
    {
        $model = $this->getByExternalId($externalId);
        if (!$model) {
            throw new EntityNotFoundException;
        }
        $this->loadRelated($model);

        return $model;
    }

    public function search($criteria)
    {
        $items = $this->buildQuery($criteria);
        return $items->paginate($criteria['size']);
    }

    public function buildQuery(&$criteria)
    {
        $criteria['size'] = $criteria['size'] ?? 10;
        $criteria['page'] = $criteria['page'] ?? 1;
        // $criteria['sortBy'] = $criteria['sortBy'] ?? 'created_at';
        // $criteria['sortBy'] = $criteria['sortBy'] ?? 'id';
        // $criteria['sortType'] = $criteria['sortType'] ?? 'DESC';

        $query = $this->constructQuery($criteria);
        $query = $this->includeForQuery($query);
        $query = $this->constructOrderBys($criteria, $query);
        if (!isset($criteria['sortBy'])) {
            $query = $query->orderBy('id', 'DESC');
        }
        // $query = $query->orderBy($criteria['sortBy'], $criteria['sortType']);


        return $query;
    }

    protected function constructOrderBys(&$criteria, $query)
    { }
}
