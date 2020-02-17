<?php namespace App\Services\Notify;

include app_path() . '/lib/' . "TopSdk.php";

class NotifySms extends NotifyBaseClass implements NotifyContract {

    protected $_appKeyPrefix = 'ALI_APP_KEY';

    protected $_appSecretPrefix = 'ALI_APP_SEC';

    private $_topClient = null;

    private $_request = null;

    public function __construct($appKey='', $appSecret='')
    {
        parent::__construct($appKey, $appSecret);
        $this->_topClient = new \TopClient;
        $this->_topClient->appkey = $this->_appKey;
        $this->_topClient->secretKey = $this->_appSecret;
        $this->_request = new \AlibabaAliqinFcSmsNumSendRequest;
        $this->_request->setSmsType('normal');
    }

    public function withTitle($title)
    {
        $this->_request->setSmsFreeSignName($title);
        return $this;
    }

    public function withUserId($userId)
    {
        $this->_request->setRecNum($userId);
        return $this;
    }

    public function withTempalte($templateId)
    {
        $this->_request->setSmsTemplateCode($templateId);
        return $this;
    }

    public function withTemplateData($templateData)
    {
        $encode = json_encode($templateData);
        $this->_request->setSmsParam('{"code":"1234"}');
        return $this;
    }

    public function send()
    {
        return $this->_topClient->execute($this->_request);
    }
}
