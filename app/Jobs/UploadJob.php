<?php

namespace App\Jobs;

use MyHelper;

class UploadJob extends Job
{
    private $tempFilePath;

    private $fileName;

    private $uesrId;

    /**
     * Create a new job instance.
     *
     * @param $fileName
     * @param $tempFilePath
     */
    public function __construct($fileName, $tempFilePath, $userId = null)
    {
        $this->tempFilePath = $tempFilePath;
        $this->fileName = $fileName;
        $this->uesrId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        MyHelper::uploadAliOSS($this->fileName, $this->tempFilePath, $this->uesrId);
    }
}
