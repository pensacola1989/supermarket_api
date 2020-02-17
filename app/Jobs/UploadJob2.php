<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use MyHelper;

class UploadJob2 extends Job
{

    private $realPath;

    private $fileName;

    /**
     * Create a new job instance.
     *
     * @param $fileName
     * @param $tempFilePath
     */
    public function __construct($realPath, $fileName)
    {
        $this->realPath = $realPath;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('upload job handling');
        MyHelper::uploadAliOSS2($this->realPath, $this->fileName);
    }
}
