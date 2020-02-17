<?php

namespace App\Services\Account;

use App\Services\Core\EntityRepository;
use App\Services\Exception\EntityNotFoundException;
use App\Services\MsgNotify\MsgNotifyContract;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 5:00 PM
 */
class UserRepository extends EntityRepository implements UserContract
{

    private $loginModel;

    protected $msgNotifyRespository;

    /**
     * UserRepository constructor.
     * @param User $model
     * @param Login $login
     */
    public function __construct(User $model, Login $login, MsgNotifyContract $msgNotifyContract)
    {
        $this->model = $model;
        $this->loginModel = $login;
        $this->msgNotifyRespository = $msgNotifyContract;
    }

    /**
     * Fetch all users;
     * @return User
     */
    public function getUserAll()
    {
        return $this->getAll();
    }


    /**
     * Add a User
     * @param $userData
     * @return mixed
     */
    public function createUser(array $userData)
    {
        return $this->createModel($userData);
        // dd(\DB::getQueryLog());
    }

    /**
     * update a user's info by user id
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateUserInfo($id, $data)
    {
        return $this->updateModel($id, $data);
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['name'])) {
            $query = $query->where('name', $criteria['name']);
        }

        if (isset($criteria['placeId'])) {
            $query = $query
                ->whereHas('subscribes', function ($q) use ($criteria) {
                    $q->where('place_subscribe.place_id', $criteria['placeId']);
                })
                ->with(['subscribes' => function ($q) use ($criteria) {
                    $q->where('places.id', $criteria['placeId']);
                }]);
        }

        if (isset($criteria['searchName'])) {
            $query = $query->where('name', 'like', '%' . $criteria['searchName'] . '%');
        }

        if (isset($criteria['adminId'])) {
            $query = $query->where('id', '<>', $criteria['adminId']);
        }

        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['Avatar']);

        return $query;
    }

    public function getUserByMobile($mobile)
    {
        return $this->model->where('mobile', $mobile)->first();
    }

    protected function loadRelated($entity)
    {
        $entity->load('Avatar');
    }

    public function getUserSummary($userId)
    {
        $queryable = $this->model->where('external_id', $userId)->select('id')->first();
        //        return $this->model->where('external_id', $userId)->select('id')->first()->Likes()->get()->count();
        $summary['postCount'] = $queryable->Posts()->count();
        $summary['likeCount'] = $queryable->Likes()->count();
        //        $beLikes = $queryable->Posts()->with(['Likes', function ($query) {
        //            return $query;
        //        }])->get();
        //        $beLikes = $queryable->Posts()->withCount('Likes')->get();
        //        $beLikes = $queryable->Posts()->withCount('Likes')->get();
        //        $likesCount = $beLikes->sum('likes_count');
        //        $userPostIds = $queryable->Posts()->lists('id');

    }

    private function _getUserNotify($userId)
    {
        $user = $this->requireById($userId);
        $syncRet = $user->NotifyRegister()->firstOrNew([
            'user_id' => $userId
        ]);
        $syncRet->save();

        return $syncRet;
    }

    public function getOpenId($userId)
    {
        $user = $this->model->find($userId);

        return $user ? $user->Logins()->where('external_system', 1)->first()->external_id : null;
    }

    public function getNotifyCount($userId)
    {
        $syncRet = $this->_getUserNotify($userId);
        $user = $syncRet->User;
        $newPoint = Carbon::parse($syncRet->updated_at)->toDateTimeString();
        // $count = $user->MsgNotifies()->where('created_at', '>', $newPoint)->count();
        $count = $this->msgNotifyRespository->getNotifyCount($user->id, $newPoint);
        if ($count > 0) {
            $syncRet->is_read = false;
        }
        return $count;
    }

    public function clearNotify($userId)
    {
        $syncRet = $this->_getUserNotify($userId);
        $syncRet->is_read = true;

        $syncRet->save();
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        return $query;
    }
}
