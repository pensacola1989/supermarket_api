<?php

namespace App\Services\Facade;

use Illuminate\Support\Facades\Facade;

class WeChatNotifyFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'weChatNotify';
    }
}
