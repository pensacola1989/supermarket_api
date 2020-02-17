<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 3:17 PM
 */

namespace App\Http\Controllers;

use App\Exceptions\UserErrors;
use App\Http\Transformers\CommentTransformer;
use App\Services\Comment\CommentContract;
use App\Services\Post\PostContract;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;
use App\Services\Comment\CommentLikeRequest;
use App\Services\Comment\CommentLikeContract;
use Illuminate\Support\Collection;
use App\Services\Comment\CommentRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentController extends Controller
{
    //     const VALIDATE_RULE = [
    //         'content' => 'required',
    //         'post_id' => 'required|alpha_num|exists:posts,id',
    // //        'from_uid' => 'required|alpha_num|exists:users,id',
    //         'to_uid' => 'required|alpha_num|exists:users,id',
    //     ];

    protected $commentRepository;

    protected $postRepository;

    protected $commentLikeRepository;

    protected $commentTransformer;

    public function __construct(CommentContract $commentContract, PostContract $postContract, CommentTransformer $commentTransformer, CommentLikeContract $commentLikeContract)
    {
        $this->commentRepository = $commentContract;
        $this->postRepository = $postContract;
        $this->commentTransformer = $commentTransformer;
        $this->commentLikeRepository = $commentLikeContract;

        // $this->middleware('wechat-auth', ['except' => ['search', 'all', 'show']]);
        // $this->middleware('auth:api', ['except' => ['search', 'all', 'show']]);
    }

    public function getMyComments(Request $request)
    {
        $request->merge([
            'fromUserId' => $this->getCurrentUserId(),
            'shouldFlatternComments' => true,
            'userCanCheckAll' => true
        ]);

        // \DB::enableQueryLog();
        $myComments = $this->commentRepository->search($request->input());
        // dd(\DB::getQueryLog());

        return $this->respondTimeLine($myComments, $this->commentTransformer);
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        $filter = function ($item) {
            return $item->id;
        };
        if ($data instanceof Collection) {
            $commentIds = $data->map($filter)->toArray();
        } elseif (is_array($data)) {
            $commentIds = collect($data)->map($filter)->toArray();
        } else {
            $commentIds = [$data->id];
        }
        if ($this->getCurrentUserId()) {
            $userCommentLikes = $this->commentLikeRepository->getUserLikesByCommentIds($commentIds, $this->getCurrentUserId());
            $transformer->setUserCommentLlikes($userCommentLikes);
        }

        if ($this->commentTransformer->getShouldIncludeUserCommentsCount()) {
            $userIds = collect($data)
                ->map(function ($d) {
                    return $d->from_uid;
                })
                ->toArray();

            $userCommentsCount = $data ?  $this->commentRepository->getUserCommentsCountOfPost($userIds, $data[0]->post_id) : [];

            $transformer->setUserCommentCountForPost($userCommentsCount);
        }

        return fractal($data, $transformer)->parseIncludes('post')->toArray();
    }

    public function getsinceCommentIdOfPost($postId)
    {
        $model = $this->commentRepository->getModel();

        return $model->where('post_id', $postId)->max('id');
    }

    public function search(Request $request)
    {

        $ret = $this->commentRepository->search($request->input());

        $this->commentTransformer->setUserSession($this->getCurrentUser());

        $this->commentTransformer->setShouldIncludeUserCommentsCount($request->has('shouldIncludeUserCommentsCount'));

        return $this->respondTimeLine($ret, $this->commentTransformer);
    }

    public function create(CommentRequest $request)
    {
        $post = $this->postRepository->requireById($request->input('post_id'));
        if ($request->input('to_uid') == 0) {
            // anonymous
            // $toUid = $this->postRepository->requireById($request->input('post_id'))->User->id;
            $toUid = $post->Usre->id;
            $request->merge(['to_uid' => $toUid]);
        }

        $data = $request->input();
        $data['from_uid'] = $this->getCurrentUserId();
        $data['visible'] = $post->is_default_comment_visible;
        $comment = $this->commentRepository->createComment($data);
        $this->commentTransformer->setUserSession($this->getCurrentUser());

        return $this->respond(fractal($comment, $this->commentTransformer));
    }

    public function like(CommentLikeRequest $commentLikeRequest, $commentId)
    {
        // $comment = $this->commentRepository->requireByExternalId($commentId);
        $commentLiked = $this->commentLikeRepository->getUserCommentLike($commentId, $this->getCurrentUserId());

        if ($commentLiked) {
            $this->commentLikeRepository->delete($commentLiked);
        } else {
            $this->commentLikeRepository->createCommentLike([
                'comment_id' => $commentId,
                'is_like' => $commentLikeRequest->input('is_like', 1),
                'user_id' => $this->getCurrentUserId()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        // $comment = $this->commentRepository->update($id, $request->input());
        $comment = $this->commentRepository->requireById($id);
        if ($comment->shouldUpdateVisible($request->input('is_top'))) {
            $request->merge(['visible' => 1]);
        }
        $comment->slientUpdate($request->input());

        return $this->transformData($comment, $this->commentTransformer);
    }

    public function all()
    {
        return $this->commentRepository->getAll();
    }

    public function destroy($id)
    {
        $comment = $this->commentRepository->requireById($id);
        if ($this->getCurrentUser()->cannot('delete-comment', $comment)) {
            throw UserErrors::NoPermission()->toException();
        }
        $this->commentRepository->delete($comment);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->commentRepository->requireById($id);

        if ($this->getCurrentUserId()) {
            $userCommentLikes = $this->commentLikeRepository->getUserLikesByCommentIds([$id], $this->getCurrentUserId());
            $this->commentTransformer->setUserCommentLlikes($userCommentLikes);;
        }

        return fractal($model, $this->commentTransformer)->parseIncludes('post');
    }
}
