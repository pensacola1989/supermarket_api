<?php

namespace App\Http\Transformers;

use App\Services\Comment\Comment;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    private $postTransformer;

    private $userTransformer;

    public function __construct(PostTransformer $postTransformer, UserTranformer $userTranformer)
    {
        $this->postTransformer = $postTransformer;
        $this->userTransformer = $userTranformer;
    }

    protected $availableIncludes = [
        'post',
        // 'subComments'
    ];

    protected $defaultIncludes = [
        'fromUser',
        'toUser',
        'parent',
        'photo',
        'subComments'
    ];

    private $_userCommentLikes = null;

    private $_shouldStopInclude = false;

    private $_userSession = null;

    private $_userCommentCountForPost = null;

    private $_shouldIncludeUserCommentsCount = false;

    public function setUserCommentCountForPost($userCommentsCountForPost)
    {
        $this->_userCommentCountForPost = $userCommentsCountForPost;

        return $this;
    }

    public function setShouldIncludeUserCommentsCount($tag)
    {
        $this->_shouldIncludeUserCommentsCount = $tag;

        return $this;
    }

    public function getShouldIncludeUserCommentsCount()
    {
        return $this->_shouldIncludeUserCommentsCount;
    }

    public function setUserCommentLlikes($userCommentLikes)
    {
        $this->_userCommentLikes = $userCommentLikes;

        return $this;
    }

    public function setUserSession($userSession)
    {
        $this->_userSession = $userSession;

        return $this;
    }

    public function transform(Comment $comment)
    {
        $base =  [
            'hasLike' => $this->_userCommentLikes ? collect($this->_userCommentLikes)->contains('comment_id', $comment->id) : false,
            'content' => $comment->content,
            'createdAt' => $comment->created_at ? $comment->created_at->toDateTimeString() : null,
            'formUid' => $comment->from_uid,
            'toUid' => $comment->to_uid,
            'id' => $comment->id,
            'postId' => $comment->post_id,
            'disLikeCount' => $comment->dis_like_count,
            'likeCount' => $comment->like_count,
            'replyCount' => $comment->reply_count,
            'replyId' => $comment->reply_id,
            'timeDiff' => timeDiffForHuman($comment->created_at),
            'canDelete' => $this->_userSession ? $this->_checkCommentCanDelete($comment) : false,
            'isTop' => $comment->is_top,
            'visible' => $comment->visible
            // 'visible' => $comment->visible
            // 'subComments' => $comment->subComments
        ];
        if ($this->_userCommentCountForPost && isset($this->_userCommentCountForPost[$comment->from_uid])) {
            $base['userCommentsCount'] = $this->_userCommentCountForPost[$comment->from_uid];
        }

        return $base;
    }

    /**
     * include from user
     * should process anonymous check
     *
     * @param Comment $comment
     * @return void
     */
    public function includeFromUser(Comment $comment)
    {
        $userTransformer = new UserTranformer();
        if ($comment->Post && $comment->Post->is_anonymous) {
            if ($comment->from_uid === $comment->Post->User->id) {
                $userTransformer->setAnonymous(true);
            }
        }
        return $this->item($comment->FromUser, $userTransformer);
    }

    /**
     * include to user
     * should process anonymous check
     *
     * @param Comment $comment
     * @return void
     */
    public function includeToUser(Comment $comment)
    {
        // if (!$this->_shouldStopInclude) {
        $userTransformer = new UserTranformer();
        if ($comment->Post && $comment->Post->is_anonymous) {
            if ($comment->to_uid === $comment->Post->User->id) {
                $userTransformer->setAnonymous(true);
            }
        }
        return $this->item($comment->ToUser, $userTransformer);
        // }
    }

    public function includeParent(Comment $comment)
    {
        // if (!$this->_shouldStopInclude) {
        if ($comment->Parent) {
            return $this->item($comment->Parent, $this);
        }
        return null;
        // }
    }

    public function includePost(Comment $comment)
    {
        if ($comment->Post) {
            return $this->item($comment->Post, $this->postTransformer);
        }
        // return null;
    }

    public function includeSubComments(Comment $comment)
    {
        // if (!$this->_shouldStopInclude) {
        if (count($comment->subComments) > 0) {
            // Manager::setRecursionLimit(1);
            // $this->getCurrentScope()->getManager()->setRecursionLimit(1);
            // $this->_shouldStopInclude = true;
            $comment->subComments->each(function ($c) {
                $c->Parent = null;
            });

            return $this->collection($comment->subComments, $this);
        }
        // }
    }

    public function includePhoto(Comment $comment)
    {
        if ($comment->photo) {
            return $this->item($comment->photo, new AttachmentTransformer);
        }
    }

    private function _checkCommentCanDelete($comment)
    {
        if ($comment->Post && $comment->Post->Place) {
            return $this->_userSession->id === $comment->FromUser->id || $comment->Post->Place->admin_id === $this->_userSession->id;
        }
        return false;
    }
}
