<?php

namespace App\Services\Core;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 12:22 AM
 */


/** base Entity CRUD contract
 * Interface EntityContract
 * @package App\Services\Core
 */
interface EntityContract
{
    /** get models paginate
     * @param $count
     * @return mixed
     */
    public function getAllPaginated($count);

    /** get a model by Id
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /** get a model by Id, if Not found throw a Exception
     * @param $id
     * @return mixed
     */
    public function requireById($id);

    /** get a model that inject in the implements EntityRepository's __construct()
     * @return mixed
     */
    public function getModel();

    /** set a model
     * @param $model
     * @return mixed
     */
    public function setModel($model);

    /** get all model
     * @return mixed
     */
    public function getAll();

    /** get a empty new model from a array
     * @param array $attributes
     * @return mixed
     */
    public function getNew($attributes = array());

    /** save a model
     * @param $data
     * @return mixed
     */
    public function save($data);

    /**
     *
     * @param $data
     * @return new object
     */
    public function createModel($data);

    /** delete a model
     * @param $model
     * @return mixed
     */
    public function delete($model);

    /**
     * @param $criteria
     * @return mixed
     */
    public function search($criteria);

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateModel($id, $data);

    public function getByExternalId($externalId);

    /**
     * override base requireById to by uid
     * @param  bigInt $uid
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function requireByExternalId($externalId);
}
