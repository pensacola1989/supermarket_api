<?php

namespace App\Http\Transformers;

use App\Services\Account\User;
use League\Fractal\TransformerAbstract;

class UserTranformer extends TransformerAbstract
{
    protected $availableIncludes = [];

    protected $defaultIncludes = [
        // 'Avatar',
    ];

    private $isAnonymous = false;

    public function setAnonymous($isAnonymous)
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function transform(User $user)
    {
        return [
            'id' => $this->isAnonymous ? 0 : $user->id,
            'name' => $this->isAnonymous ? '匿名用户' : $user->name,
            'externalId' => $this->isAnonymous ? 0 : $user->external_id,
            'nickName' => $this->isAnonymous ? '' : $user->nick_name,
            'gender' => $user->gender,
            'createdAt' => $user->created_at ? $user->created_at->toDateString() : null,
            'avatarUrl' => $this->_getUserAvatar($user),
            'isAnonymous' => $this->isAnonymous
        ];
    }

    // public function includeAvatar(User $user)
    // {
    //     if ($user->Avatar) {
    //         return $this->item($user->Avatar, new AttachmentTransformer);
    //     }
    //     if($user->avatar_url) {

    //     }
    //     // return $user->Avatar ? $this->item($user->Avatar, new AttachmentTransformer()) : ($user->avatar_url || ($user->Logins->count() ? $user->Logins()->first()->avatar_url : ''));
    // }

    private function _getUserAvatar(User $user)
    {
        $fullUrlPrefix = config('app.oss.fullUrlPrefix');
        if ($this->isAnonymous) {
            return config('app.default_image.url') . '/' . $user->external_id . '?' . http_build_query(config('app.default_image.params'));
        }
        if ($user->avatar_url) {
            // http://ehe1989.oss-cn-hangzhou.aliyuncs.com/data/images/14/156613924475784611.jpg
            // return $user->avatar_url;
            return "http://{$fullUrlPrefix}/data/images/{$user->id}/{$user->avatar_url}";
        }
        if ($user->Avatar) {
            return "http://{$fullUrlPrefix}/data/images/{$user->id}/{$user->Avatar->attach_file_name}";
            // return $user->Avatar->attach_file_name;
        }
        if ($user->Logins()->count()) {
            return $user->Logins()->first()->avatar_url;
        }
    }
}
