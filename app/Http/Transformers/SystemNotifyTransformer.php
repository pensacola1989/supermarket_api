<?php

namespace App\Http\Transformers;

use App\Services\SystemNotify\SystemNotify;
use League\Fractal\TransformerAbstract;

class SystemNotifyTransformer extends TransformerAbstract
{

    public function __construct()
    { }


    protected $availableIncludes = [];

    protected $defaultIncludes = ['attachment'];

    public function transform(SystemNotify $systemNotify)
    {
        return [
            'id' => $systemNotify->id,
            'title' => $systemNotify->title,
            'content' => $systemNotify->content,
            'type' => $systemNotify->type,
            'visible' => $systemNotify->visible,
            'createdAt' => $systemNotify->created_at->toDateTimeString(),
            'timeDiff' => $systemNotify->created_at->toDateTimeString(),
            // 'timeDiff' => timeDiffForHuman($systemNotify->created_at),
            'updatedAt' => $systemNotify->updated_at
        ];
    }


    public function includeAttachment(SystemNotify $systemNotify)
    {
        $attachment = $systemNotify->attachment;
        if (!$attachment) {
            return null;
        }
        return $this->item($attachment, new AttachmentTransformer);
    }
}
