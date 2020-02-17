<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/20/17
 * Time: 11:05 PM
 */

namespace App\Services\Post;


use MyHelper;
use App\Services\Tag\TagRepository;

class PostObserver
{
    protected $tagRepository;

    public function __construct()
    {
        $this->tagRepository = app()->make(TagRepository::class);
    }

    public function creating($model)
    {
        $snowId = MyHelper::newId();
        $model->external_id = $snowId;
    }

    public function saved($model)
    { }
}
