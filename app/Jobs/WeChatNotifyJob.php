<?php

namespace App\Jobs;

use App\Services\WechatAuth\TemplateMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeChatNotifyJob extends Job
{
    // $memberId, $sceneKey, $businessId, $data, array$pageArgs = null
    private $jobParams;

    private $_weChatConfig;

    private $_weapp;

    private $_tplMsg;

    public function __construct($params)
    {
        $this->jobParams = $params;
        $this->_weChatConfig = config('wechat');
        $this->_tplMsg = new TemplateMessage($this->_weChatConfig);
    }

    public function handle()
    {
        Log::info('>>>>>>>>>>>>>>>handling wechat nofiy job>>>>>>>>>>>>>>>');
        try {
            $notifyParams = $this->jobParams;
            $sceneKey = $notifyParams['sceneKey'];
            $templateId = $this->_weChatConfig['scene'][$sceneKey]['templateId'];
            $data = $this->jobParams['data'];
            if (isset($this->jobParams['pageArgs'])) {
                $pagePath = $this->_weChatConfig['scene'][$sceneKey]['page'];
                $pageArgsStr = http_build_query($this->jobParams['pageArgs']);
                $pagePath .= '?' . $pageArgsStr;
                $data['page'] = $pagePath;
            }
            // $weChatResp = $this->_tplMsg->sendTemplateMessage($openId, $templateId, $formId, $this->jobParams['data']);
            $weChatResp = $this->_tplMsg->sendReqeuestSubscribeTemplateMessage($this->jobParams['openId'], $templateId, $data);
            Log::info('>>>>>>>>>>>>>>>subscribe messagesent reseponse>>>>>>>>>>>>>>>');
            Log::info((array) $weChatResp);
        } catch (\Exception $exception) {
            throw $exception;
            Log::error($exception->getMessage());
        }
    }

    // public function handle()
    // {
    //     Log::info('>>>>>>>>>>>>>>>handling wechat nofiy job>>>>>>>>>>>>>>>');
    //     try {
    //         $notifyParams = $this->jobParams;
    //         $notifyKey = "{$notifyParams['memberId']}|{$notifyParams['sceneKey']}|{$notifyParams['businessId']}";
    //         \Log::info('&&&&&&&&&&&& notify key is &&&&&&&&&&&&&&&&&&&&&&&');
    //         \Log::info($notifyKey);
    //         \Log::info(Cache::has($notifyKey));


    //         $formIdOpenId = $this->_pickOneFormIdFromCache($notifyParams['memberId']);
    //         \Log::info("formIds store ... {$formIdOpenId}");
    //         if (!Cache::has($notifyKey) && !$formIdOpenId) {
    //             return;
    //         }
    //         $sceneFormIdExist = Cache::has($notifyKey);
    //         list($formId, $openId) = explode('|', $sceneFormIdExist ? (Cache::get($notifyKey)) : $formIdOpenId);

    //         $sceneKey = $notifyParams['sceneKey'];
    //         $templateId = $this->_weChatConfig['scene'][$sceneKey]['templateId'];
    //         $data = $this->_buildData($this->jobParams['data']);
    //         if (isset($this->jobParams['pageArgs'])) {
    //             $pagePath = $this->_weChatConfig['scene'][$sceneKey]['page'];
    //             $pageArgsStr = http_build_query($this->jobParams['pageArgs']);
    //             $pagePath .= '?' . $pageArgsStr;
    //             $data['page'] = $pagePath;
    //         }
    //         $weChatResp = $this->_tplMsg->sendTemplateMessage($openId, $templateId, $formId, $data);
    //         Log::info('>>>>>>>>>>>>>>>template msg sent reseponse>>>>>>>>>>>>>>>');
    //         Log::info((array) $weChatResp);
    //         Cache::forget($notifyKey);
    //     } catch (\Exception $exception) {
    //         throw $exception;
    //         Log::error($exception->getMessage());
    //     }
    // }

    // private function _buildData($data)
    // {
    //     $ret = [];
    //     foreach ($data as $index => $value) {
    //         $index++;
    //         $ret["keyword{$index}"] = ['value' => $value];
    //     }

    //     return $ret;
    // }



    private function _pickOneFormIdFromCache($userId)
    {
        $userTimeRangeKey = "{$userId}FormIdRangekeys";
        $userFormIdsCacheKeyPrefix = "{$userId}_formIds";
        $userFormIdKeyRange = Cache::get($userTimeRangeKey);
        if (!$userFormIdKeyRange || count($userFormIdKeyRange) === 0) {
            return null;
        }
        \Log::info("User range keys....");
        \Log::info($userFormIdKeyRange);
        $validRangeKeys = array_filter($userFormIdKeyRange, function ($timeKey) use ($userFormIdsCacheKeyPrefix) {
            // return Cache::has("{$userFormIdsCacheKeyPrefix}_{$timeKey}");
            return Cache::has($timeKey);
        });

        // if filter result has no valid result,then remove cache key
        if (count($validRangeKeys) === 0) {
            Cache::forget($userTimeRangeKey);
            return null;
        }
        // set back to refresh time range keys
        Cache::put($userTimeRangeKey, $validRangeKeys);
        \Log::info("User valid range keys....");
        \Log::info($validRangeKeys);
        $oneFormIdsKey = $validRangeKeys[count($validRangeKeys) - 1];
        \Log::info("oneFormIdsKey is $oneFormIdsKey");
        $latestFormIds = Cache::get($oneFormIdsKey);
        \Log::info("before pop " . count($latestFormIds));
        $oneValidFormId = array_shift($latestFormIds);
        \Log::info("after pop " . count($latestFormIds));
        // set back to this range formIds
        Cache::put($oneFormIdsKey, $latestFormIds);
        if (count($latestFormIds) === 0) {
            // remove range keys in cache
            $removeIndex = array_search($oneFormIdsKey, $validRangeKeys);
            // unset($validRangeKeys[$removeIndex]);
            array_splice($validRangeKeys, 0, 1);
            Cache::put($userTimeRangeKey, $validRangeKeys);
            Cache::forget($oneFormIdsKey);
        }

        \Log::info(">>>>>>>>>>>>>>>>>picking formId ret is $oneValidFormId");

        return $oneValidFormId;
    }
}
