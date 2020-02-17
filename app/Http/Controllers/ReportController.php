<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:54 AM
 */

namespace App\Http\Controllers;


use App\Services\Report\ReportContract;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $validateRule = [
        // 'report_from_uid' => 'required|alpha_num|exists:users,id',
        'report_post_id' => 'required|alpha_num|exists:posts,id'
    ];

    private $reportRepostory;

    function __construct(ReportContract $reportContract)
    {
        $this->reportRepostory = $reportContract;
    }

    public function search(Request $request)
    { }

    public function create(Request $request)
    {
        $this->validate($request, $this->validateRule);
        $data = $request->input();
        $data['report_from_uid'] = $request->user()->id;
        $report = $this->reportRepostory->createReport($data);

        return $report;
    }

    public function update(Request $request, $id)
    {
        //        $this->validate($request, $this->validateRule);
        $data = $request->input();

        $report = $this->reportRepostory->requireById($id);

        $report->update($data);

        return $report;
        //        $data['report'] = $request->user()->id;
        //        $post = $this->postRepository->updatePost($id, $request->input());
        //
        //        return $post;
    }

    public function all()
    {
        //        return $this->postRepository->getAll();
    }

    public function destroy(Request $request, $id)
    {
        //        $model = $this->postRepository->requireByExternalId($id);
        //        if ($request->user()->cannot('delete-post', $model)) {
        //            throw new NotAllowException;
        //        }
        //        $this->postRepository->delete($model);
        //
        //        return $this->OK();
    }


    public function show(Request $request, $id)
    {
        //        $model = $this->postRepository->requireByExternalId($id);
        //        $userLikes = $this->likeRepository->getUserLikesByPostIds([$model->id], $request->user()->id);
        //        $model['hasLike'] = collect($userLikes)->contains('post_id', $model->id);
        //
        //        if ($model->is_anonymous == 1) {
        //            $model['user']['name'] = '匿名用户';
        //            $model->user->id = 0;
        //            $model->user->external_id = 0;
        //            $model['user']['logins'] = [];
        //        }
        //
        //        return $model;
    }
}
