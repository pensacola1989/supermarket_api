<?php namespace App\Services\Notify;

interface NotifyContract
{
    public function withUserId($userId);

    public function withTempalte($templateId);

    public function withTemplateData($templateData);

    public function send();
}
