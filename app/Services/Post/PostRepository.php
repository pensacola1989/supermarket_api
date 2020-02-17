<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:06 PM
 */

namespace App\Services\Post;

use App\Services\Account\User;
use App\Services\Core\EntityRepository;
use App\Services\Place\Place;
use App\Services\Tag\Tag;
use MyHelper;

class PostRepository extends EntityRepository implements PostContract
{
    private $userModel;

    private $placeModel;

    private $tagModel;

    public function __construct(Post $model, User $userModel, Place $placeModel, Tag $tag)
    {
        $this->model = $model;
        $this->userModel = $userModel;
        $this->placeModel = $placeModel;
        $this->tagModel = $tag;
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['placeId'])) {
            $query = $query->where('place_id', '=', $criteria['placeId']);
            // stick top is a seprated query ,not in this method
            // $query = $query->where('top', 0);
        }
        if (isset($criteria['maxId'])) {
            $query = $query->where('id', '<', $criteria['maxId']);
        }
        if (isset($criteria['sinceId'])) {
            $query = $query->where('id', '>', $criteria['sinceId']);
        }
        if (isset($criteria['sinceTime'])) {
            $query = $query->where('updated_at', '>', $criteria['sinceTime'])->where('top', 0);
        }
        if (isset($criteria['maxTime'])) {
            $query = $query->where('updated_at', '<', $criteria['maxTime']);
        }
        if (isset($criteria['tagId'])) {
            $query = $query
                ->whereHas('Tags', function ($q) use ($criteria) {
                    $q->where('tags.id', $criteria['tagId']);
                })
                ->whereHas('Place', function ($q) {
                    $q->where('is_private', 0);
                });
        }
        if (isset($criteria['lat']) && isset($criteria['lng']) && isset($criteria['sinceDistance'])) {
            $lat = $criteria['lat'];
            $lng = $criteria['lng'];
            $query = $query
                ->selectRaw(
                    "*,st_distance(
                        point(?,?),
                        point(lat,lng)
                    ) * 111195 AS distance",
                    [$lat, $lng]
                )
                ->having('distance', '>', $criteria['sinceDistance'])
                ->having('distance', '<', config('app.maxDistance') * 1000);
        }
        if (isset($criteria['lat']) && isset($criteria['lng']) && isset($criteria['nearbySinceId'])) {
            $lat = $criteria['lat'];
            $lng = $criteria['lng'];
            $query = $query
                ->selectRaw(
                    "*,st_distance(
                        point(?,?),
                        point(lat,lng)
                    ) * 111195 AS distance",
                    [$lat, $lng]
                )
                ->having('distance', '<', config('app.maxDistance') * 1000)
                ->where('id', '>', $criteria['nearbySinceId']);
        }
        if (isset($criteria['hasMultiPlaces']) && isset($criteria['userId'])) {
            $user = $this->userModel->findOrFail($criteria['userId']);
            $placeIds = $user->HistoryViews()->pluck('place_id');
            $query = $query->whereIn('place_id', $placeIds);
        } elseif (isset($criteria['isLike']) && isset($criteria['userId'])) {
            $query = $query
                ->whereIn('id', function ($q) use ($criteria) {
                    $q->select('post_id')
                        ->from(with(new Like)->getTable())
                        ->where('user_id', $criteria['userId'])
                        ->whereNotIn('post_id', function ($sq) use ($criteria) {
                            $sq->select('id')
                                ->from(with(new Post)->getTable())
                                ->where('user_id', $criteria['userId']);
                        });
                });
        } elseif (isset($criteria['userId'])) {
            $query = $query->where('user_id', $criteria['userId']);
        }

        if (isset($criteria['justPhotos'])) {
            $query = $query->has('Photos', '>', 0);
        }

        // filter the posts from block
        if (isset($criteria['blockedPlaceIds'])) {
            $query = $query->whereHas('Place', function ($q) use ($criteria) {
                $q->whereNotIn('id', $criteria['blockedPlaceIds']);
            });
        }

        if (isset($criteria['blockedUserIds'])) {
            $query = $query->whereHas('User', function ($q) use ($criteria) {
                $q->whereNotIn('id', $criteria['blockedUserIds']);
            });
        }

        if (isset($criteria['isMp'])) {
            // if post were linked to mp ,which means mp_link field in is not null
            $query = $query->whereNotNull('mp_link');
        }

        if (!isset($criteria['userId']) && !isset($criteria['placeId'])) {
            $query = $query->whereHas('Place', function ($q) {
                $q->where('is_private', 0);
            });
        }

        return $query;
    }

    public function getMyNewArticleCommentsCount($adminId)
    {
        $placesAdminByMe = $this->model->ofAdmin($adminId);
        if ($placesAdminByMe->count() === 0) {
            return 0;
        }
        $newCommentsCountOfArticle = $placesAdminByMe->ofAritcleUnRead()->count();

        return $newCommentsCountOfArticle;
    }

    public function getBoardIndex($placeId, $criteria)
    {
        $query = $this->model;
        $query = $query->where('place_id', '=', $criteria['placeId'])->where('top', 0);
        $criteria['size'] = $criteria['size'] ?? 10;
        $criteria['page'] = $criteria['page'] ?? 1;
        $criteria['sortBy'] = 'updated_at';
        $criteria['sortType'] = 'DESC';
    }

    public function getPosterBlockUsers($userId)
    {
        return $this->userModel->find($userId)->blocks();
    }



    protected function includeForQuery($query)
    {
        $query = $query->with(['Photos', 'User', 'User.Logins', 'User.Avatar', 'Place', 'Tags']);

        return $query;
    }

    public function createPost($attribute)
    {
        $place = $this->placeModel->findOrFail($attribute['place_id']);
        $user = $this->userModel->findOrFail($attribute['user_id']);
        $photoIds = $attribute['photo_ids'];
        unset($attribute['photo_ids']);
        $post = $this->getNew($attribute);
        if (isset($attribute['lat']) && isset($attribute['lng'])) {
            $post->geo_hash = MyHelper::convertGeoToHash($attribute['lat'] . ',' . $attribute['lng']);
        }
        $post->Place()->associate($place);
        $post->User()->associate($user);
        $post->User->load('Logins');
        $post->save();
        $post->Photos()->attach($photoIds);
        if (isset($attribute['tag_ids'])) {
            $post->Tags()->attach($attribute['tag_ids']);
            $this->tagModel->whereIn('id', $attribute['tag_ids'])->increment('post_count');
        }

        $newPost = $this->requireById($post->id);

        return $newPost;
    }

    public function updatePost($postId, $attribute)
    {
        $place = $this->placeModel->findOrFail($attribute['place_id']);
        $user = $this->userModel->findOrFail($attribute['user_id']);
        $post = $this->requireById($postId);
        $photoIds = $attribute['photo_ids'];
        unset($attribute['photo_ids']);

        $post->update($attribute);
        $post->Place()->associate($place);
        $post->User()->associate($user);
        $post->save();
        $post->Photos()->sync($photoIds);
        $post->load('Photos');

        return $post;
    }

    public function getNearbySinceId($latlng)
    {
        list($lat, $lng) = explode(',', $latlng);
        $postsInRange = $this->model
            ->selectRaw(
                "id,st_distance(
                        point(?,?),
                        point(lat,lng)
                    ) * 111195 AS distance",
                [$lat, $lng]
            )
            ->having('distance', '>=', 0)
            ->having('distance', '<', config('app.maxDistance') * 1000);

        $retWithMaxId = $this->model
            ->selectRaw('MAX(posts.id) as maxId')
            ->joinSub($postsInRange, 'postInRange', function ($join) {
                $join->on('posts.id', '=', 'postInRange.id');
            })
            ->first();
        return $retWithMaxId ?  $retWithMaxId->maxId : null;
    }

    public function inrementCommentNumber($postId)
    {
        $this->model->where('id', $postId)->first()->increment('comment_number');
    }

    public function decrementCommentNumber($postId)
    {
        $this->model->find($postId)->decrement('comment_number');
    }

    public function getStickTops($placeId)
    {
        return $this->model
            ->where('place_id', $placeId)
            ->where('top', '<>', 0)
            ->orderBy('top', 'asc')
            ->get();
    }

    public function getPhotoWall($placeId, $pageSize = 5)
    { }

    protected function loadRelated($entity)
    {
        // comment need to be got seprated , becuase comments has much more, need a pagination
        $entity->load(['User', 'User.Logins', 'User.Avatar', 'Place', 'Likes', 'Photos', 'Tags']);
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        if (isset($criteria['placeId']) && !isset($criteria['shouldNotSortTop'])) {
            $query = $query->orderBy('top', 'desc');
        }
        if (isset($criteria['sortBy']) && $criteria['sortBy'] === 'updated_at') {
            $query = $query->orderBy('updated_at', 'DESC');
        }
        if (isset($criteria['sortBy']) && $criteria['sortBy'] === 'distance') {
            $query  = $query->orderBy('distance', 'asc');
        }
        // dd($query->count());
        // $query = $query->orderBy('comment_number', 'desc')->orderBy('like_number', 'desc');
        // $query =  $query->orderBy('updated_at', 'desc');

        return $query;
    }
}
