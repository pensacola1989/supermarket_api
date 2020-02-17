<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/29/17
 * Time: 8:37 PM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\LikeTransformer;
use App\Services\Post\LikeContract;
use App\User;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    private $likeRepository;

    protected $likeTransformer;

    public function __construct(LikeContract $likeContract, LikeTransformer $likeTransformer)
    {
        $this->likeRepository = $likeContract;
        $this->likeTransformer = $likeTransformer;
    }

    public function search(Request $request)
    {
        $likes = $this->likeRepository->search($request->input());

        return $this->respondPaginate($likes, $this->likeTransformer);
    }

    public function create(Request $request)
    {

    }

    public function update(Request $request, $id)
    {
    }

    public function all()
    {
        return $this->postRepository->getAll();
    }

    public function show(Request $request, $id)
    {
//        $model = $this->postRepository->requireByExternalId($id);
        //        $userLikes = $this->likeRepository->getUserLikesByPostIds([$model->id], $request->user()->id);
        //        $model['hasLike'] = collect($userLikes)->contains('post_id', $model->id);
        //
        //        if ($model->is_anonymous == 1) {
        //            $model['user']['name'] = '匿名用户';
        //            $model->user->id = 0;
        //            $model->user->external_id = 0;
        //            $model['user']['logins'] = [];
        //        }
        //
        //        return $model;
    }

}
