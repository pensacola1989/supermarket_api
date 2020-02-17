<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/23/17
 * Time: 10:50 AM
 */

namespace App\Services\WechatAuth;

use App\Exceptions\ApiException;
use App\Exceptions\SystemErrors;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class TemplateMessage
{
    private $templateMessageUrl;
    private $accessTokenUrl;
    private $tplConfig;
    private $appId;
    private $secret;
    private $subscribeMessageUrl;
    private $getTemplateUrl;

    function __construct($wxConfig)
    {
        $this->templateMessageUrl = $wxConfig['template_message_url'];
        $this->subscribeMessageUrl = $wxConfig['subscribe_message_url'];
        $this->getTemplateUrl = $wxConfig['template_get'];
        $this->tplConfig = include 'TemplateConfig.php';
        $this->accessTokenUrl = $wxConfig['access_token_url'] ?? '';
        $this->appId = $wxConfig["appid"] ?? "";
        $this->secret = $wxConfig["secret"] ?? "";
    }

    private function constructMessage($msgType, $msgBody)
    {
        $tpl = $this->tplConfig[$msgType];
        $tpl['page'] = $msgBody['page'] ?? '';
        if (count($msgBody['data']) > 0) {
            foreach ($msgBody['data'] as $data) {
                $val = $data['value'] ?? '';
                $color = $data['color'] ?? '';
                $tpl['data']['value'] = $val;
                $tpl['data']['color'] = $color;
            }
        }
        $tpl['touser'] = $msgBody['openId'];
        $tpl['form_id'] = $msgBody['formId'];

        return $tpl;
    }

    public function getAccessToken()
    {
        if ($cachedToken = Cache::get('access_token')) {
            return $cachedToken;
        }
        $accessTokenUrl = sprintf($this->accessTokenUrl, $this->appId, $this->secret);
        $json = Curl::to($accessTokenUrl)->get();
        $ret = json_decode($json, true);
        if (!isset($ret['access_token'])) {
            throw new \Exception('weixin_accesstoken_got_failed');
        }
        $this->accessToken = $ret['access_token'];
        $cacheExpiresAt = Carbon::now()->addMinutes(100);
        Cache::Put('access_token', $this->accessToken, $cacheExpiresAt);

        return $this->accessToken;
    }

    // public function sendTemplateMessage($msgType, $openId, $data){
    //     $templateData['openId'] = $openId;
    //     $templateData['data'] = $data;

    //     $msgForSend = $this->constructMessage($msgType, $templateData);
    //     $msgUrl = sprintf($this->templateMessageUrl, $this->getAccessToken());
    //     $json = Curl::to($msgUrl)
    //         ->withData($msgForSend)
    //         ->asJson()
    //         ->post();
    //     dd($json);
    // }
    public function sendReqeuestSubscribeTemplateMessage($openId, $templateId, $data)
    {
        try {
            // $templates = $this->getTemplates();
            // Log::info($templates);
            $templateData['template_id'] = $templateId;
            $templateData['touser'] = $openId;
            $templateData['data'] = $data;
            if (isset($data['page'])) {
                $templateData['page'] = $data['page'];
                unset($templateData['data']['page']);
            }
            Log::info(json_encode($templateData, 6));
            $msgUrl = sprintf($this->subscribeMessageUrl, $this->getAccessToken());
            $json = Curl::to($msgUrl)
                ->withData($templateData)
                ->asJson()
                ->post();

            if (isset($json->errcode) && $json->errcode === 40001) {
                throw SystemErrors::WeChatAcessTokenError($json->errmsg)->toException(null);
            }

            return $json;
        } catch (\Exception $err) {
            Log::info($err->getMessage());
            if ($err instanceof ApiException) {
                $apiError = $err->getError();
                if ($apiError->getErrorCode() === '1003') {
                    Cache::forget('access_token');
                    $apiError = $err->getError();
                    $msgUrl = sprintf($this->templateMessageUrl, $this->getAccessToken());
                    $json = Curl::to($msgUrl)
                        ->withData($templateData)
                        ->asJson()
                        ->post();

                    return $json;
                }
                throw $err;
            }
        }
    }

    public function getTemplates()
    {
        try {
            $msgUrl = sprintf($this->getTemplateUrl, $this->getAccessToken());
            $json = Curl::to($msgUrl)->get();

            return $json;
        } catch (\Exception $err) {
            Log::info($err->getMessage());
            throw $err;
        }
    }



    public function sendTemplateMessage($openId, $templateId, $formId, $data)
    {
        try {
            $templateData['template_id'] = $templateId;
            $templateData['touser'] = $openId;
            $templateData['form_id'] = $formId;
            $templateData['data'] = $data;
            if (isset($data['page'])) {
                $templateData['page'] = $data['page'];
            }
            Log::info($templateData);
            $msgUrl = sprintf($this->templateMessageUrl, $this->getAccessToken());
            $json = Curl::to($msgUrl)
                ->withData($templateData)
                ->asJson()
                ->post();

            if (isset($json->errcode) && $json->errcode === 40001) {
                throw SystemErrors::WeChatAcessTokenError($json->errmsg)->toException(null);
            }

            return $json;
        } catch (\Exception $err) {
            Log::info($err->getMessage());
            if ($err instanceof ApiException) {
                $apiError = $err->getError();
                if ($apiError->getErrorCode() === '1003') {
                    Cache::forget('access_token');
                    $apiError = $err->getError();
                    $msgUrl = sprintf($this->templateMessageUrl, $this->getAccessToken());
                    $json = Curl::to($msgUrl)
                        ->withData($templateData)
                        ->asJson()
                        ->post();

                    return $json;
                }
                throw $err;
            }
        }
    }
}
