<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 11:15 PM
 */

namespace App\Http\Controllers;

use App\Exceptions\UserErrors;
use App\Http\Transformers\PostTransformer;
use App\Http\Transformers\TagTransformer;
use App\Services\Exception\NotAllowException;
use App\Services\Place\PlaceContract;
use App\Services\Post\LikeContract;
use App\Services\Post\PostContract;
use App\Services\Post\PostRequest;
use App\Services\Tag\TagRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Transformers\PhotoWallTransformer;
use App\Services\Place\LikeRequest;
use App\Services\Account\UserContract;
use App\Services\Facade\WeChatNotifyFacade;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // private $validateRule = [
    //     'content' => 'required',
    //     'place_id' => 'required|alpha_num|exists:places,id',
    // ];

    private $postRepository;

    private $likeRepository;

    private $postTransformer;

    private $photoWallTransformer;

    protected $tagTransformer;

    protected $placeRepository;

    protected $tagRepository;

    protected $userRepository;

    public function __construct(
        PostContract $postContract,
        LikeContract $likeContract,
        PostTransformer $postTransformer,
        PlaceContract $placeContract,
        TagRepository $tagRepository,
        TagTransformer $tagTransformer,
        PhotoWallTransformer $photoWallTransformer,
        UserContract $userContract
    ) {
        $this->postRepository = $postContract;
        $this->likeRepository = $likeContract;
        $this->postTransformer = $postTransformer;
        $this->placeRepository = $placeContract;
        $this->tagRepository = $tagRepository;
        $this->tagTransformer = $tagTransformer;
        $this->photoWallTransformer = $photoWallTransformer;
        $this->userRepository = $userContract;
        // $this->middleware('wechat-auth', ['except' => ['all', 'show']]);
        // $this->middleware('auth:api', ['except' => ['all', 'show']]);
    }

    public function search(Request $request)
    {
        $request->merge([
            'blockedPlaceIds' => $this->getVisitorBlockedPlaceIds(),
            'blockedUserIds' => $this->getPosterBlockUsers()
        ]);
        // \DB::enableQueryLog();
        $ret = $this->postRepository->search($request->input());
        // dd(\DB::getQueryLog());


        return $this->respondTimeLine($ret, $this->postTransformer);
    }

    public function searchNearBy(Request $request, $latlng)
    {
        list($lat, $lng) = explode(',', $latlng);
        $request->merge([
            'sortBy' => 'distance',
            'sortType' => 'ASC',
            'lat' => $lat,
            'lng' => $lng,
            'sinceDistance' => $request->has('sinceDistance') ? $request->input('sinceDistance') : 0,
            'blockedPlaceIds' => $this->getVisitorBlockedPlaceIds()
        ]);
        $ret = $this->postRepository->search($request->input());
        return $this->respondNearby($ret, $this->postTransformer, $latlng);
    }

    public function loadNearyByNew(Request $request, $latlng)
    {
        list($lat, $lng) = explode(',', $latlng);
        $request->merge([
            'sortBy' => 'distance',
            'sortType' => 'ASC',
            'lat' => $lat,
            'lng' => $lng,
            'blockedPlaceIds' => $this->getVisitorBlockedPlaceIds()
        ]);
        $ret = $this->postRepository->search($request->input());
        return $this->respondNearby($ret, $this->postTransformer, $latlng);
    }

    public function getStickTopPostsByPlaceId($placeExtId)
    {
        $placeId = $this->placeRepository->requireByExternalId($placeExtId)->id;
        $stickTopPosts = $this->postRepository->getStickTops($placeId);

        return $this->transformData($stickTopPosts, $this->postTransformer);
    }

    public function getMyLikePosts(Request $request)
    {
        $request->merge(['isLike' => true, 'userId' => $this->getCurrentUserId()]);
        $myLikePosts = $this->postRepository->search($request->input());

        return $this->respondTimeLine($myLikePosts, $this->postTransformer);
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        $filter = function ($item) {
            return $item->id;
        };
        if ($data instanceof Collection) {
            $postIds = $data->map($filter)->toArray();
        } elseif (is_array($data)) {
            $postIds = collect($data)->map($filter)->toArray();
        } else {
            $postIds = [$data->id];
        }
        $currentUserId = $this->getCurrentUserId();
        if ($currentUserId) {
            $userLikes = $this->likeRepository->getUserLikesByPostIds($postIds, $this->getCurrentUserId());
            $transformer->setUserId($this->getCurrentUserId())->setUserLikes($userLikes);
        }

        return parent::transformData($data, $transformer);
    }

    private function respondNearby(LengthAwarePaginator $paginator, TransformerAbstract $tranformer, $latlng, $headers = [])
    {
        $data = $this->paginate($paginator, $tranformer, null);
        $data['loadMoreDistance'] = $this->_getSinceDistance($data['list']);
        $data['nearbySinceId'] = $this->postRepository->getNearbySinceId($latlng);
        $response = response()->json($data, $this->getStatusCode(), $headers);
        $response->header('X-' . config('app.name') . '-ErrorCode', 0);

        return $response;
    }

    protected function _getSinceDistance(&$items)
    {
        return collect($items)->max('distanceRaw');
    }

    public function create(PostRequest $request)
    {
        $data = $request->input();
        $data['user_id'] = $this->getCurrentUserId();
        $data['lat'] = $request->input('latitude');
        $data['lng'] = $request->input('longitude');
        $post = $this->postRepository->createPost($data);

        WeChatNotifyFacade::collectCredentials($request, config('wechat.scenes.comment'), $post->id);

        return $this->transformData($post, $this->postTransformer);
    }

    public function update(Request $request, $id)
    {
        if ($request->has('mp_link')) {
            if (!isValidMpLink($request->input('mp_link'))) {
                throw UserErrors::MpLinkIsNotValid()->toException();
            }
        }
        $post = $this->postRepository->updateModel($id, $request->input());

        return $this->transformData($post, $this->postTransformer);
    }

    public function all()
    {
        return $this->postRepository->getAll();
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->postRepository->requireByExternalId($id);
        if ($request->user()->cannot('delete-post', $model)) {
            throw new NotAllowException;
        }
        $this->postRepository->delete($model);

        return $this->OK();
    }

    public function getMyNewArticleCommentsCount(Request $request)
    {
        return $this->postRepository->getMyNewArticleCommentsCount($this->getCurrentUserId());
    }

    public function like(LikeRequest $request, $postId)
    {
        $post = $this->postRepository->requireByExternalId($postId);
        $liked = $this->likeRepository->getUserPostLike($post->id, $this->getCurrentUserId());
        if ($liked) {
            $this->likeRepository->delete($liked);
        } else {
            $this->likeRepository->createLike([
                'post_id' => $post->id,
                'is_like' => $request->input('is_like', 1),
                'user_id' => $this->getCurrentUserId(),
            ]);
        }

        return $this->OK();
    }

    public function show(Request $request, $id)
    {
        $model = $this->postRepository->requireByExternalId($id);
        $this->postTransformer->setShowFullContent(true);

        if ($this->getCurrentUser()) {
            $this->postTransformer->setUserSession($this->getCurrentUser());
        }
        return $this->transformData($model, $this->postTransformer);
    }

    public function getBoardIndex(Request $request, $placeId)
    {
        $request->merge([
            'placeId' => $placeId
        ]);
        // \DB::enableQueryLog();

        $ret = $this->postRepository->search($request->input());
        // dd(\DB::getQueryLog());
        return $this->respondByActiveTimeLine($ret, $this->postTransformer);
    }

    public function stickTop($postId)
    {
        $post = $this->postRepository->updateModel($postId, ['top' => 1]);

        return $this->transformData($post, $this->postTransformer);
    }

    public function searchMpLinkPost(Request $request)
    {
        $request->merge([
            'userId' => $this->getCurrentUserId(),
            'isMp' => 1,
            'sortBy' => 'updated_at'
        ]);

        $ret = $this->postRepository->search($request->input());

        return $this->respondTimeLine($ret, $this->postTransformer);
    }

    public function searchText(Request $request)
    {
        if (!$request->has('searchText') || $request->input('searchText') === '') {
            return $this->respondPaginate(new LengthAwarePaginator([], 0, 8), $this->postTransformer);
        }
        $text = $request->input('searchText');
        $postRawQuery = $this->postRepository->getModel()->where('content', 'like', "%{$text}%");
        if ($request->has('place_id')) {
            $postRawQuery = $postRawQuery->where('place_id', $request->input('place_id'));
        } else {
            $postRawQuery = $postRawQuery->where('user_id', $this->getCurrentUserId());
        }
        $postRawQuery = $postRawQuery->whereNotNull('mp_link');

        return $this->respondPaginate($postRawQuery->paginate(8), $this->postTransformer);
    }

    public function getPhotoWall(Request $request, $id)
    {
        // $criteria = $request->input();
        // $criteria['justPhotos'] = true;
        $request->merge([
            'justPhotos' => true,
            'placeId' => $id
        ]);
        $postShowInPhotoWall = $this->postRepository->search($request->input());
        $ret = $postShowInPhotoWall
            ->map(function ($data) {
                collect($data->Photos)
                    ->map(function ($p) use ($data) {
                        $p->soucePostId = $data->external_id;
                        $p->desc = $data->content;
                    });
                return $data->Photos;
            })
            ->flatten();

        return $this->respondTimeLine($ret, $this->photoWallTransformer);
    }

    public function readComments(Request $request, $postId)
    {
        $post = $this->postRepository->requireByExternalId($postId);
        $post->article_unread = 0;

        $post->save();

        return $this->respond([]);
    }

    private function getVisitorBlockedPlaceIds()
    {
        if ($userId = $this->getCurrentUserId()) {
            return $this->userRepository->requireById($userId)->blocks()->pluck('place_id');
        }
        return [];
    }

    private function getPosterBlockUsers()
    {
        if ($userId = $this->getCurrentUserId()) {
            return $this->userRepository->requireById($userId)->blockUsers()->pluck('block_user_id');
        }
        return [];
    }
}
