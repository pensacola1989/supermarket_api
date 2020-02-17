<?php

namespace App\Http\Transformers;

use App\Services\Attachments\Attachment;
use League\Fractal\TransformerAbstract;

// avatar: {
//     id: 3, type: "image/jpg", …
// attach_file_name: "http://ehe1989.oss-cn-hangzhou.aliyuncs.com/9f2f070828381f301a86a652a0014c086f06f0aa.jpg"
// created_at: null
// external_id: 123123123
// height: null
// id: 3
// type: "image/jpg"
// updated_at: null
// width: null
// }
class AttachmentTransformer extends TransformerAbstract
{
    private $useDefault;

    public function setDefault($useDefault)
    {
        $this->$useDefault = $useDefault;
    }

    protected $availableIncludes = [];

    public function transform(Attachment $attachment)
    {
        return [
            'externalId' => $attachment->external_id,
            'id' => $attachment->id,
            'url' => $this->_getUrl($attachment),
            'height' => $attachment->hight,
            'mime' => $attachment->type,
            'width' => $attachment->width,
        ];
    }

    private function _getUrl($attachment)
    {
        if ($this->useDefault) {
            $defaultImageUrl = config('app.default_image.url');
            $params = config('app.default_image.params');
            return "  $defaultImageUrl/{$attachment->external_id}?s={$params['s']}&d={$params['d']}&r={$params['r']}";
        }
        return config('app.image_url_base') . '/' . config('app.directory.image') . '/' . $attachment->user_id . '/' . $attachment->attach_file_name;
    }
}
