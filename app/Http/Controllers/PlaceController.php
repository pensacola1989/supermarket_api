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
use App\Services\Account\UserContract;
use App\Http\Transformers\UserTranformer;
use App\Http\Transformers\PlaceSubscriberTransformer;
use App\Services\HistoryPlace\HistoryPlaceContract;
use Illuminate\Support\Facades\Artisan;

class PlaceController extends Controller
{
    const validateRule = [
        'category_id' => 'required|alpha_num|exists:place_categories,id',
        'name' => 'required|between:1,50',
        'lat' => 'regex:/^[+-]?\d+\.\d+/',
        'lng' => 'regex:/^[+-]?\d+\.\d+/',
    ];

    protected $placeRepository;
    protected $postRepository;
    protected $placeCategoryRepository;
    protected $placeTransfomer;
    protected $placeSummaryTransformer;
    protected $userRepository;
    protected $userTransformer;
    protected $placeSubscriberTransformer;
    protected $historyPlaceRespository;

    public function __construct(
        PlaceContract $contractRepo,
        PlaceCategoryContract $placeCategoryRepo,
        PostRepository $postRepository,
        PlaceTransfomer $placeTransfomer,
        PlaceSummaryTransformer $placeSummaryTransformer,
        UserContract $userContract,
        UserTranformer $userTranformer,
        PlaceSubscriberTransformer $placeSubscriberTransformer,
        HistoryPlaceContract $historyPlaceContract
    ) {
        $this->middleware('auth:admin', ['except' => ['show', 'search', 'subscribe']]);
        $this->middleware('wechat-auth', ['only' => ['show', 'subscribe']]);
        $this->middleware('auth:api', ['only' => ['show', 'subscribe']]);

        $this->placeRepository = $contractRepo;
        $this->placeCategoryRepository = $placeCategoryRepo;
        $this->postRepository = $postRepository;
        $this->placeSummaryTransformer = $placeSummaryTransformer;
        $this->placeTransfomer = $placeTransfomer;
        $this->userRepository = $userContract;
        $this->userTransformer = $userTranformer;
        $this->placeSubscriberTransformer = $placeSubscriberTransformer;
        $this->historyPlaceRespository = $historyPlaceContract;
    }

    public function search(Request $request)
    {
        if ($request->has('adminByMe')) {
            $request->merge([
                'currentUserId' => $this->getCurrentUserId()
            ]);
        }
        $ret = $this->placeRepository->search($request->input());
        $this->placeTransfomer->setGeo($request->has('latlng'));

        return $this->respondPaginate($ret, $this->placeTransfomer);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, self::validateRule);
        $place = $this->placeRepository->createPlace($request->input());

        return $place;
    }


    public function update(PlaceUpdateRequest $request, $placeId)
    {
        $entity = $request->toEntity();
        // $this->validate($request, self::validateRule);
        $place = $this->placeRepository->updatePlace($placeId, $entity);

        return $place;
    }

    public function all()
    {
        return $this->placeRepository->getAll();
    }

    public function destroy($id)
    {
        $place = $this->placeRepository->requireById($id);
        $this->placeRepository->delete($place);

        return $this->OK();
    }

    public function show(Request $request, $id)
    {
        $place = $this->placeRepository->requireByExternalId($id);
        $this->placeTransfomer->setPlaceSummary(true);
        if ($this->getCurrentUser()) {
            $this->placeTransfomer->setUserSession($this->getCurrentUser())->setIncludeConfigs(true);
            if ($this->getCurrentUser()->cannot('check-quan-index', $place)) {
                throw UserErrors::userIsBlockedForThisPlace()->toException();
            }
        }
        return $this->respond(fractal($place, $this->placeTransfomer));
    }

    public function addApplySubscribe(Request $request, $placeId)
    {
        $place = $this->placeRepository->requireByExternalId($placeId);
        $userHasApplied = $this->placeRepository->userHasApplied($this->getCurrentUserId(), $place->id);
        if ($userHasApplied) {
            throw UserErrors::UserCannotRepeatDoThisAction("apply to subscribe a place")->toException();
        }

        return $this->placeRepository->addPlaceSubApply($this->getCurrentUserId(), $place->id);
    }

    // public function approveApplySubscribe(Request $request, $placeId, $userId)
    // {
    //     $this->placeRepository->approvePlaceSubApply($userId, $placeId);

    //     return $this->respond([]);
    // }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        return parent::transformData($data, $transformer);
    }

    public function subscribe(Request $request, $id)
    {
        $place = $this->placeRepository->requireById($id);
        $exist = $place->subscribes()->where('sub_user_id', $request->user()->id)->first();
        $exist ? $exist->delete() : $this->placeRepository->subscribePlace($request->user()->id, $id);

        return $this->OK();
    }

    public function summary(Request $request, $placeId)
    {
        $place = $this->placeRepository->requireByExternalId($placeId);
        $this->placeTransfomer->setIncludeAdminSummary(true)->setIncludeConfigs(true);
        if ($this->getCurrentUser()) {
            $this->placeTransfomer->setUserSession($this->getCurrentUser());
        }
        return $this->respond(fractal($place, $this->placeTransfomer));
        // $place = $this->placeRepository->requireByExternalId($id);

        // return $this->respond(fractal($place, $this->placeSummaryTransformer));
    }

    public function searchSubscriber(Request $request)
    {
        $request->query->set('placeId', $request->place->id);
        $request->query->set('adminId', $request->place->admin_id);

        $ret = $this->userRepository->search($request->query());

        return $this->respondPaginate($ret, $this->placeSubscriberTransformer);
    }
}
