<?php namespace App\Services\Notify;

abstract class NotifyBaseClass
{

    protected $_appKeyPrefix = '';

    protected $_appSecretPrefix = '';

    protected $_appKey = '';

    protected $_appSecret = '';

    public function __construct($appKey, $appSecret)
    {
        $this->_appKey = $appKey ? $appKey : $this->getEnvConfig($this->_appKeyPrefix);
        $this->_appSecret = $appSecret ? $appSecret : $this->getEnvConfig($this->_appSecretPrefix);
        
        if (!$this->checkCredential($this->_appKey, $this->_appSecret)) {
            throw new \Exception('Invalid appId or appSecret');
        }
    }

    protected function getEnvConfig($key)
    {
        return env($key);
    }

    protected function checkCredential($appKey, $appSecret)
    {
        return !$this->_IsNullOrEmptyString($appKey)
            && !$this->_IsNullOrEmptyString($appSecret);
    }

    protected function _IsNullOrEmptyString($question)
    {
        return (!isset($question) || trim($question)==='');
    }
}
