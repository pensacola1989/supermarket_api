<?php

namespace App\Http\Transformers;

use App\Services\Account\Block\UserBlock;
use League\Fractal\TransformerAbstract;

class UserBlockTransformer extends TransformerAbstract
{
    protected $userTransformer;

    protected $availableIncludes = [];

    protected $defaultIncludes = [
        'blockedUser'
    ];

    public function __construct(UserTranformer $userTranformer)
    {
        $this->userTransformer = $userTranformer;
    }

    public function transform(UserBlock $block)
    {
        return [
            'id' => $block->id,
            'createdAt' => $block->created_at ? $block->created_at->toDateString() : null
        ];
    }

    public function includeBlockedUser(UserBlock $block)
    {
        return $this->item($block->blockedUser, $this->userTransformer);
    }
}
