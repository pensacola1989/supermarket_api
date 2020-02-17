<?php

namespace App\Services\WechatAuth;


use Illuminate\Support\Facades\Log;

class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    function __construct($appid, $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        $this->appid = $appid;
    }


    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return ErrorCode::$IllegalAesKey;
        }
        $aesKey = base64_decode($this->sessionKey);


        if (strlen($iv) != 24) {
            return ErrorCode::$IllegalIv;
        }
        Log::info('...........$iv is........' . json_encode($iv));
        $aesIV = base64_decode($iv);
        Log::info('...........$aesIV is........' . json_encode($aesIV));
        $aesCipher = base64_decode($encryptedData);
        Log::info('...........$aesCipher is........' . json_encode($aesCipher));
        $pc = new Prpcrypt($aesKey);
        $result = $pc->decrypt($aesCipher, $aesIV);

        if ($result[0] != 0) {
            return $result[0];
        }

        $dataObj = json_decode($result[1]);
        Log::info('...........data obj is........' . json_encode($dataObj));
        if ($dataObj == NULL) {
            Log::error('.................................');
            return ErrorCode::$IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return ErrorCode::$IllegalBuffer;
        }
        $data = $result[1];
        return ErrorCode::$OK;
    }

    public function decrypted_openssl($appid, $sessionKey, $encryptedData, $iv)
    {
        if (strlen($appid) == 0 || strlen($sessionKey) != 24 || strlen($iv) != 24 || strlen($encryptedData) == 0) {
            return false;
        }
        $encrypted = base64_decode($encryptedData);
        $aesiv = base64_decode($iv);
        if (strlen($aesiv) < 4 || strlen($aesiv) > 16) {
            return false;
        }
        $result = openssl_decrypt($encrypted, 'aes-128-cbc', base64_decode($sessionKey), OPENSSL_RAW_DATA, $aesiv);
        //string openssl_encrypt ( string $data , string $method , string $key [, int $options = 0 [, string $iv = "" [, string &$tag = NULL [, string $aad = "" [, int $tag_length = 16 ]]]]] )
        //tag_length
        //The length of the authentication tag. Its value can be between 4 and 16 for GCM mode.
        if (!$result) {
            return false;
        }
        $result = json_decode($result);
        if (empty($result)) {
            return false;
        }
        if ($result->watermark->appid != $appid) {
            return false;
        }
        return $result;
    }
}