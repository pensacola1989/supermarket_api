<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/16/17
 * Time: 12:44 PM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\PlaceTransfomer;
use App\Services\Place\PlaceCategoryContract;
use App\Services\Place\PlaceContract;
use App\Services\Post\PostRepository;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;
use App\Exceptions\UserErrors;
use App\Http\Transformers\PlaceSummaryTransformer;
use App\Services\Place\PlaceUpdateRequest;
use App\Services\Place\PlaceSubApplyContract;
use App\Http\Transformers\PlaceSubApplyTransformer;
use App\Services\HistoryPlace\HistoryPlaceContract;

class PlaceSubApplyController extends Controller
{
    protected $placeSubApplyRepository;

    protected $placeSubApplyTransformer;

    protected $placeRepository;

    protected $historyRespository;

    public function __construct(PlaceSubApplyContract $placeSubApplyContract, PlaceSubApplyTransformer $placeSubApplyTransformer, PlaceContract $placeContract, HistoryPlaceContract $historyPlaceContract)
    {
        $this->placeSubApplyRepository = $placeSubApplyContract;
        $this->placeSubApplyTransformer = $placeSubApplyTransformer;
        $this->placeRepository = $placeContract;
        $this->historyRespository = $historyPlaceContract;
    }

    public function search(Request $request, $id)
    { }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        return parent::transformData($data, $transformer);
    }

    public function create(Request $request)
    { }

    public function update(Request $request, $externalId)
    { }

    public function all()
    { }

    public function destroy($id)
    { }

    public function show(Request $request, $placeId)
    {
        $place = $this->placeRepository->requireByExternalId($placeId);
        $request->query->set('placeId', $place->id);
        $ret = $this->placeSubApplyRepository->search($request->query());

        // return $this->respondTimeLine($ret, $this->placeSubApplyTransformer);
        return $this->respondPaginate($ret, $this->placeSubApplyTransformer);
    }

    public function approveApplySubscribe(Request $request, $placeId, $userId)
    {
        $place = $this->placeRepository->requireByExternalId($placeId);

        $this->placeSubApplyRepository->approvePlaceSubApply($userId, $place->id);

        // 同步浏览记录
        $this->historyRespository->syncUserHistory($userId, $place->id);

        return $this->respond([]);
    }
}
