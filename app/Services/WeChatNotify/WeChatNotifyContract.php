<?php

namespace App\Services\WeChatNotify;

use Illuminate\Http\Request;

interface WeChatNotifyContract
{
    public function collectCredentials(Request $request, $sceneKey, $businessId);

    public function deliveryMsg($memberId, $sceneKey, $businessId, $data, array $pageArgs = null);
}
