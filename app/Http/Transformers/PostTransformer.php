<?php

namespace App\Http\Transformers;

use App\Services\Post\Post;
use League\Fractal\TransformerAbstract;

// canDelete ?: boolean;
// title ?: string;
// content: string;
// place_id: number;
// user_id: number;
// external_id: string;
// updated_at: Date;
// created_at: Date;
// id ?: number;
// place ?: Place;
// hasLike ?: boolean;
// like_number ?: number;
// comment_number ?: number;
// user ?: User;
// photos ?: array < Photo >;
class PostTransformer extends TransformerAbstract
{
    protected $userTranformer;

    protected $tagTransfomer;

    protected $defaultIncludes = [
        'place',
        'photos',
        'user',
        'tags',
    ];

    protected $availableIncludes = [];

    protected $userLikes = null;

    protected $userId = null;

    protected $showFullContent = false;

    protected $placeTransfomer = null;

    private $userSession = null;

    public function __construct(PlaceTransfomer $placeTransfomer, UserTranformer $userTranformer, TagTransformer $tagTransformer)
    {
        $this->placeTransfomer = $placeTransfomer;
        $this->userTranformer = $userTranformer;
        $this->tagTransfomer = $tagTransformer;
    }

    public function setUserLikes($userLikes)
    {
        $this->userLikes = $userLikes;

        return $this;
    }

    public function setUserSession($userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }

    public function setShowFullContent($tag)
    {
        $this->showFullContent = $tag;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function transform(Post $post)
    {
        $postResponse = [
            'canDelete' => $this->userCanDelete($post),
            'id' => $post->id,
            'title' => $post->title,
            'content' => $this->fixText($post->content),
            // 'place' => $post->Place,
            'hasLike' => $this->userLikes ? collect($this->userLikes)->contains('post_id', $post->id) : false,
            'likeNumber' => $post->like_number,
            'commentNumber' => $post->comment_number,
            'user' => $post->User,
            'photos' => null,
            'externalId' => $post->external_id,
            'placeId' => $post->place_id,
            'userId' => $post->user_id,
            'isOverflow' => isTextOverflow($post->content),
            'isTop' => $post->top,
            'isAnonymous' => $post->is_anonymous,
            'createdAt' => $post->created_at ? $post->created_at->toDateTimeString() : null,
            'updatedAt' => $post->updated_at ? $post->updated_at->toDateTimeString() : null,
            'timeDiff' => timeDiffForHuman($post->created_at),
            'mpLink' => $post->mp_link,
            'isMpLink' => $post->mp_link !== null,
            'hasLinked' => isValidMpLink($post->mp_link),
            'canReply' => $this->_userCanReply($post),
            'isDefaultVisible' => $post->is_default_comment_visible,
            'hasUnreadComments' => $post->article_unread
        ];
        if ($post->distance) {
            $postResponse['distance'] = formatDistance($post->distance);
            $postResponse['distanceRaw'] = ceil($post->distance);
        }
        if ($post->lat && $post->lng) {
            $postResponse['location'] = [
                'name' => $post->location_name,
                'lat' => floatval($post->lat),
                'lng' => floatval($post->lng)
            ];
        }

        return $postResponse;
    }

    public function includePlace(Post $post)
    {
        $place = $post->Place;
        if (!$place) {
            return null;
        }
        if ($this->userSession) {
            $this->placeTransfomer->setUserSession($this->userSession);
        }
        return $this->item($place, $this->placeTransfomer);
    }

    public function includePhotos(Post $post)
    {
        $photos = $post->Photos;
        return $this->collection($photos, new AttachmentTransformer);
    }

    public function includeUser(Post $post)
    {
        return $this->item($post->User, $this->userTranformer->setAnonymous($post->is_anonymous));
    }

    public function includeTags(Post $post)
    {
        return $this->collection($post->Tags, $this->tagTransfomer);
    }

    private function fixText($text)
    {
        if ($this->showFullContent) {
            return $text;
        } else {
            return isTextOverflow($text) ? mb_strimwidth($text, 0, 240, '...', 'utf8') : $text;
        }
    }

    private function userCanDelete($post)
    {
        return $post->User->id === $this->userId || ($post->Place && $post->Place->admin_id === $this->userId);
    }

    /**
     * 是否可以回复评论
     * 优先级 平台的配置 > 管理员 > 普通用户(板块设置可互评&&帖子设置可互评)
     * @param Post $post
     * @return Boolean
     */
    private function _userCanReply($post)
    {
        if (!$post->Place) {
            return false;
        }
        $currentUserIsAdmin = $this->userSession && $this->userSession->id === $post->place->admin_id;
        return env('PL_CAN_REPLY')
            && ($currentUserIsAdmin || (getPlaceConfigDirectlyValues($post->Place->configs, 1) && $post->can_reply));
    }
}
