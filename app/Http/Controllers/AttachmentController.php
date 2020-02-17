<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/19/17
 * Time: 9:32 PM
 */

namespace App\Http\Controllers;

use App\Jobs\UploadJob2;
use App\Jobs\UploadJob;
use App\Services\Attachments\AttachContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use MyHelper;
use App\Http\Transformers\PhotoWallTransformer;

class AttachmentController extends Controller
{

    private $photoWallTransformer;

    protected $attachmentRepository;

    protected $ossClient;

    public function __construct(AttachContract $attachContract, PhotoWallTransformer $photoWallTransformer)
    {
        $this->attachmentRepository = $attachContract;
        $this->photoWallTransformer = $photoWallTransformer;
    }

    public function getPhotoWall(Request $request)
    {
        $ret = $this->attachmentRepository->search($request->input());

        return $this->respondTimeLine($ret, $this->photoWallTransformer);
        // return $ret;
        // dd($ret);
    }

    public function uploadAttachment2(Request $request)
    {
        try {
            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                throw new Exception('file not specified or not valid!');
            }
            $mime = $request->file('file')->getClientMimeType();
            // $fileName = MyHelper::GUID() . '.' . $request->file('file')->getClientOriginalExtension();
            $fileName = time() . rand(10000, 99999999) . '.' . $request->file('file')->getClientOriginalExtension();
            $snowId = MyHelper::newId();

            // $filePath = storage_path() . '/uploads/' . Date('Ymd') . '/' . $fileName;
            //如果未来加视频需要修改
            list($width, $height) = getimagesize($request->file('file')->getPathname());
            Log::info('====width is ' . $width . '  height is ' . $height . '====');
            $attachment = $this->attachmentRepository->createModel([
                'type' => $mime,
                'attach_file_name' => $fileName,
                'external_id' => $snowId,
                'width' => $width,
                'height' => $height,
                'user_id' => $this->getCurrentUserId(),
            ]);

            $attachment->url = 'http://' . env('OSS_BUCK_NAME') . '.' . env('OSS_END_POINT') . '/' . $fileName;
            $attachment->thumbnail = $attachment->url . '?x-oss-process=image/resize,w_500,limit_0';

            // $request->file('file')->move(storage_path() . '/uploads/' . Date('Ymd') . '/', $fileName);
            $job = (new UploadJob2($request->file('file')->getRealPath(), $fileName))->onQueue('upload');
            dispatch($job);

            return $attachment;
        } catch (OssException $e) {
            throw $e;
        }
    }

    public function uploadAttachment(Request $request)
    {
        try {
            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                throw new Exception('file not specified or not valid!');
            }
            $mime = $request->file('file')->getClientMimeType();
            $fileName = time() . rand(10000, 99999999) . '.' . $request->file('file')->getClientOriginalExtension();
            $snowId = MyHelper::newId();

            $filePath = storage_path() . '/uploads/' . Date('Ymd') . '/' . $fileName;
            //如果未来加视频需要修改
            list($width, $height) = getimagesize($request->file('file')->getPathname());
            Log::info('====width is ' . $width . '  height is ' . $height . '====');
            $attachment = $this->attachmentRepository->createModel([
                'type' => $mime,
                'attach_file_name' => $fileName,
                'external_id' => $snowId,
                'width' => $width,
                'height' => $height,
                'user_id' => $this->getCurrentUserId(),
            ]);

            // $attachment->url = 'http://' . env('OSS_BUCK_NAME') . '.' . env('OSS_END_POINT') . '/' . $fileName;
            $attachment->url = config('app.image_url_base') . '/' . config('app.directory.image') . '/' . $attachment->user_id . '/' . $attachment->attach_file_name;
            $attachment->thumbnail = $attachment->url . '?x-oss-process=image/resize,w_500,limit_0';

            $request->file('file')->move(storage_path() . '/uploads/' . Date('Ymd') . '/', $fileName);
            //            $filePath = storage_path() . '/uploads/' . Date('Ymd') . '/' . $fileName;

            //            //如果未来加视频需要修改
            //            list($with, $height) = getimagesize($filePath);
            //            Log::info('====width is ' . $with . '  height is ' . $height . '====');

            // Queue::push(new UploadJob($fileName, $filePath));

            // 这段代码在fpm模式下跑，但是queuejob的目录跟fpm模式下在容器内部的目录不一致（但是映射的是一致的），所以做了目录适配
            $filePath = str_replace('var/www', 'usr/src', $filePath);
            $job = (new UploadJob($fileName, $filePath, $this->getCurrentUserId()))->onQueue('upload');
            dispatch($job);

            return $attachment;
        } catch (OssException $e) {
            throw $e;
        }
    }
}
