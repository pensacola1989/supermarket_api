<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 7:44 PM
 */

namespace App\Services\HistoryPlace;

//use App\Services\HistoryPlace\HistoryPlace;
use App\Services\Account\User;
use App\Services\Core\EntityRepository;
use App\Services\Place\Place;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistoryPlaceRepository extends EntityRepository implements HistoryPlaceContract
{
    private $_userModel;

    private $_placeModel;

    public function __construct(HistoryPlace $model, User $userModel, Place $placeModel)
    {
        $this->model = $model;
        $this->_userModel = $userModel;
        $this->_placeModel = $placeModel;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['place_id'])) {
            $query = $query->where('place_id', $criteria['place_id']);
        }
        if (isset($criteria['user_id'])) {
            $query = $query->where('user_id', $criteria['user_id']);
        }
        // 如果是用于发帖的选项,那么要取canPost为true/1的。
        //      * 1 -> canReply
        //      * 2 -> canPost
        //      * 3 -> canAnonymous
        if (isset($criteria['optionForPublish'])) {
            $query = $query->whereHas('Places', function ($q) {
                // $q->whereRaw("JSON_CONTAINS (configs, '{\"configId\": 3, \"configValue\": 1}' )");
                $q->whereRaw("JSON_CONTAINS (configs, '{\"configId\": 2, \"configValue\": 1}' )");
            });
        }
        if (isset($criteria['adminByMe']) && !$criteria['adminByMe'] && isset($criteria['user_id'])) {
            $query = $query->whereHas('Places', function ($q) use ($criteria) {
                $q->where('admin_id', '<>', $criteria['user_id']);
            });
        }
        // if (isset($criteria['userSubscribePlaceIds']) && count($criteria['userSubscribePlaceIds']) > 0) {
        //     $userSubscribePlaceIds = $criteria['userSubscribePlaceIds'];
        //     $query->each(function ($history) use ($userSubscribePlaceIds) {
        //         $history->has_sub = collect($userSubscribePlaceIds)->contains($history->Places->id);
        //     });
        // }

        return $query;
    }

    public function getMyHistoryViews($userId, $isAdmin, $page = 1, $size = 6)
    {
        // DB::enableQueryLog();
        $result =  $this->model->with('Places')
            ->select(DB::raw('COUNT(if(history_views.updated_at < posts.created_at,1, null)) AS count'), 'history_views.*')
            ->leftJoin('places', 'history_views.place_id', '=', 'places.id')
            ->leftJoin('posts', 'history_views.place_id', '=', 'posts.place_id')
            ->where('history_views.user_id', $userId)
            ->where('places.admin_id',  $isAdmin ? '=' : '<>', $userId)
            ->groupBy('places.id')
            ->orderByRaw('count DESC')
            ->orderByRaw('history_views.updated_at DESC')
            ->paginate($size);
        // dd(\DB::getQueryLog());

        return $result;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['Places', 'Places.avatar']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        // TODO: Implement loadRelated() method.
    }

    public function syncUserHistory(int $userId, int $placeId)
    {
        $user = $this->_userModel->findOrFail($userId);
        $place = $this->_placeModel->findOrFail($placeId);
        $history = $this->model->firstOrNew(['place_id' => $placeId, 'user_id' => $userId]);
        $history->updated_at = Carbon::now();

        $history->Users()->associate($user);
        $history->Places()->associate($place);
        $history->save();

        $history->load('Places');

        return $history;
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        if (isset($criteria['sortBy'])) {
            $query = $query->orderBy($criteria['sortBy'], 'DESC');
        }
        return $query;
    }
}
