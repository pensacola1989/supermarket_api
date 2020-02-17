<?php

namespace App\Http\Transformers;

use App\Services\Post\Post;
use League\Fractal\TransformerAbstract;

class PhotoWallTransformer extends TransformerAbstract
{
    protected $postTransformer;

    protected $attachmentTransformer;

    protected $defaultIncludes = [
        'post',
        'attachment'
    ];

    protected $availableIncludes = [];

    protected $userId = null;

    protected $userLikes = null;


    public function __construct(PostTransformer $postTransformer, AttachmentTransformer $attachmentTransformer)
    {
        $this->postTransformer = $postTransformer;
        $this->attachmentTransformer = $attachmentTransformer;
    }

    public function setUserLikes($userLikes)
    {
        $this->userLikes = $userLikes;

        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * this $post should have photos
     *
     * @param Post $post
     * @return array
     */
    public function transform($photo): array
    {
        return [
            // 'post' => $this->item($photo->Post, $this->postTransformer)
            // 'sourcePostId' => $photo->post->external_id,
            // 'desc' => $photo->post->content
        ];
        // $originPost = $this->item($post, $this->postTransformer);
        // return $originPost;
        // dd($originPost);
        // $postResponse = [];
        // if ($post->distance) {
        //     $postResponse['distance'] = formatDistance($post->distance);
        //     $postResponse['distanceRaw'] = ceil($post->distance);
        // }

        // return $postResponse;
    }

    public function includePost($photo)
    {
        if ($photo->Post) {
            return $this->collection($photo->Post, $this->postTransformer);
        }
        // return null;
    }

    public function includeAttachment($photo)
    {
        return $this->item($photo, $this->attachmentTransformer);
    }
}
