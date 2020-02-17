<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17
 * Time: 9:40 PM
 */

namespace App\Http\Controllers;


use App\Services\WechatAuth\TemplateMessage;
use App\Services\WechatAuth\WechatAuth;
use Illuminate\Http\Request;

class TestController
{
    public function index(Request $request)
    {
//        app()->make(TemplateMessage::class)
//            ->sendTemplateMessage('reply', 'ohZYa0T-IE0TgbSMHEAdUNmAGPMY', [
//
//            ]);
//        app()->make(WechatAuth::class)->getAccessToken();
        $openId = $request->headers->get('openId');
        $user = $request->user();

        return compact('openId', 'user');
    }
}