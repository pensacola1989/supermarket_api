<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/4/17
 * Time: 10:33 AM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\NotifyTransformer;
use App\Services\MsgNotify\MsgNotifyContract;
use Illuminate\Http\Request;

class MsgNotifyController extends Controller
{
    private $msgNotifyRepository;

    private $notifyTransformer;

    public function __construct(MsgNotifyContract $msgNotifyContract, NotifyTransformer $notifyTransformer)
    {
        $this->msgNotifyRepository = $msgNotifyContract;
        $this->notifyTransformer = $notifyTransformer;
    }

    public function search(Request $request)
    {
        $ret = $this->msgNotifyRepository->search($request->input());

        return $this->respondTimeLine($ret, $this->notifyTransformer);
    }

    // private function _generateLoadRange(&$items)
    // {
    //     $maxId = collect($items)->min('id');
    //     $sinceId = collect($items)->max('id');

    //     return [$sinceId, $maxId];
    // }

    public function create(Request $request)
    { }

    public function update(Request $request, $id)
    { }

    public function all()
    {
        //        return $this->userRepository->getAll();
    }

    public function destroy($id)
    {
        //        $model = $this->userRepository->requireById($id);
        //        $this->userRepository->delete($model);
        //
        //        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->msgNotifyRepository->requireById($id);
        //
        return $model;
    }
}
