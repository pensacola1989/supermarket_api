<?php

namespace App\Services\WechatAuth;


use Illuminate\Support\Facades\Cache;
use Ixudra\Curl\Facades\Curl;
use Mockery\Exception;

class WechatAuth
{
    private $appId;
    private $secret;
    private $code2session_url;
    private $openId;
    private $sessionKey;
    private $authInfo;
    private $accessToken;
    private $accessTokenUrl;

    function __construct($wxConfig)
    {
        $this->appId = $wxConfig["appid"] ?? "";
        $this->secret = $wxConfig["secret"] ?? "";
        $this->code2session_url = $wxConfig["code2session_url"] ?? "";
        $this->accessTokenUrl = $wxConfig['access_token_url'] ?? '';
    }


    public function getLoginInfo($code)
    {
        $this->authCodeAndCode2session($code);
        return $this->authInfo;
    }

    public function getUserInfo($encryptedData, $iv)
    {
        $pc = new WXBizDataCrypt($this->appId, $this->sessionKey);
        $decodeData = "";
        $errCode = $pc->decryptData($encryptedData, $iv, $decodeData);
        if ($errCode != 0) {
            throw new \Exception('weixin_decode_fail');
        }
        return $decodeData;
    }



    private function authCodeAndCode2session($code)
    {
        $code2session_url = sprintf($this->code2session_url, $this->appId, $this->secret, $code);
        $jsonData = Curl::to($code2session_url)->get();
        $this->authInfo = json_decode($jsonData, true);
        if (!isset($this->authInfo['openid'])) {
            throw new \Exception('weixin_session_expired');
        }
        $this->openId = $this->authInfo['openid'] ?? '';
        $this->sessionKey = $this->authInfo['session_key'] ?? '';
    }

}