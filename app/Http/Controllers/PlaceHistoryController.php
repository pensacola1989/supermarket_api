<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 9:04 PM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\HistoryTransformer;
use App\Services\Account\UserContract;
use App\Services\HistoryPlace\HistoryPlaceContract;
use App\Services\Place\PlaceContract;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

class PlaceHistoryController extends Controller
{
    protected $historyRepository;

    protected $placeRepository;

    protected $userRepository;

    protected $historyTransformer;

    public function __construct(
        HistoryPlaceContract $contract,
        PlaceContract $placeContract,
        UserContract $userContract,
        HistoryTransformer $historyTransformer
    ) {
        $this->historyRepository = $contract;
        $this->placeRepository = $placeContract;
        $this->userRepository = $userContract;
        $this->historyTransformer = $historyTransformer;
    }

    public function create(Request $request)
    {
        $placeId = $request->input('place_id');
        $userId = $request->user()->id;

        $ret = $this->historyRepository->syncUserHistory($userId, intval($placeId));
        return $this->OK();
    }

    public function getMyViewHistory(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 6);
        $isAdmin = $request->input('adminByMe', false);
        $isAdmin = $isAdmin && $isAdmin === 'false' ? 0 : 1;
        $viewsWithNewPostCount =  $this->historyRepository->getMyHistoryViews($this->getCurrentUserId(), $isAdmin, $page, $size);
        // $viewsWithNewPostCount->each(function ($view) {
        //     $view->load('Places');
        // });

        return $this->respondPaginate($viewsWithNewPostCount, $this->historyTransformer);
    }


    public function search(Request $request)
    {
        $request->merge([
            'user_id' => $request->user()->id,
        ]);

        $ret = $this->historyRepository->search($request->input());

        return $this->respondPaginate($ret, $this->historyTransformer);
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        $userSubscribePlaceIds = $this->getCurrentUser()->PlaceSubscribe()->pluck('place_id');
        $data = collect($data)
            ->each(function ($history) use ($userSubscribePlaceIds) {
                $history->has_sub = collect($userSubscribePlaceIds)->contains($history->Places->id);
            })
            ->sortByDesc('has_sub')
            // ->sortByDesc('updated_at')
            ->values()
            ->all();

        return parent::transformData($data, $transformer);
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->historyRepository->requireById($id);
        $this->historyRepository->delete($model);
        $request->user()->PlaceSubscribe()->where('place_id', $model->Places->id)->delete();

        return $this->OK();
    }
}
