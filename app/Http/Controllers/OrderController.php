<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:54 AM
 */

namespace App\Http\Controllers;

use App\Services\Order\OrderCreateRequest;
use App\Services\Order\OrderSearchRequest;
use App\Services\Order\OrderService;
use App\Services\Order\OrderTransformer;
use App\Services\Order\OrderUpdateRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    private $orderService;

    private $orderTransformer;

    function __construct(OrderService $orderService, OrderTransformer $orderTransfomer)
    {
        $this->orderService = $orderService;
        $this->orderTransformer = $orderTransfomer;
    }

    public function search(OrderSearchRequest $request)
    {
        return $this->respondPaginate(
            $this->orderService->search(
                $request->toEntity()
            ),
            $this->orderTransformer
        );
    }

    public function create(OrderCreateRequest $request)
    {
        $model = $request->toEntity();
        $model['custom_id'] = $this->getCurrentUserId();

        $newOrder =  $this->orderService->createOrder($model);

        return $this->respond(fractal($newOrder, $this->orderTransformer)->parseIncludes('items'));
    }

    public function update(OrderUpdateRequest $request, $orderSn)
    {
        $model = $request->toEntity();

        $updatedOrder = $this->orderService->updateOrder($orderSn, $model);

        return $this->respond(fractal($updatedOrder, $this->orderTransformer));
    }

    public function getOrderByOrderSn($orderSn)
    {
        return $this->respond(
            fractal(
                $this->orderService->getOrderByOrderSn($orderSn),
                $this->orderTransformer
            )->parseIncludes('items,custom,store,payScreeShot')
        );
    }

    public function all()
    {
        //        return $this->postRepository->getAll();
    }

    public function destroy(Request $request, $id)
    {
        //        $model = $this->postRepository->requireByExternalId($id);
        //        if ($request->user()->cannot('delete-post', $model)) {
        //            throw new NotAllowException;
        //        }
        //        $this->postRepository->delete($model);
        //
        //        return $this->OK();
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
