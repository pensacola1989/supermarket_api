<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/16/17
 * Time: 12:44 PM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\PlaceTransfomer;
use App\Services\Facade\WeChatNotifyFacade;
use App\Services\Place\PlaceCategoryContract;
use App\Services\Place\PlaceContract;
use App\Services\Post\PostRepository;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;
use App\Services\Place\PlaceApplyRequest;
use App\Services\Place\PlaceApplyRespository;
use Illuminate\Support\Facades\Artisan;

class PlaceApplyController extends Controller
{
    const validateRule = [
        'category_id' => 'required|alpha_num|exists:place_categories,id',
        'name' => 'required|between:1,50|unique:place_applies,name',
        'lat' => 'regex:/^[+-]?\d+\.\d+/',
        'lng' => 'regex:/^[+-]?\d+\.\d+/',
    ];

    private $placeRepository;

    private $placeApplyRespository;

    private $postRepository;

    private $placeCategoryRepository;

    private $placeTransfomer;

    public function __construct(
        PlaceContract $contractRepo,
        PlaceCategoryContract $placeCategoryRepo,
        PlaceApplyRespository $placeApplyRespository,
        PostRepository $postRepository,
        PlaceTransfomer $placeTransfomer
    ) {
        // $this->middleware('auth:admin', ['except' => ['show', 'search', 'subscribe']]);
        // $this->middleware('wechat-auth', ['only' => ['show', 'subscribe']]);
        // $this->middleware('auth:api', ['only' => ['show', 'subscribe']]);

        $this->placeRepository = $contractRepo;
        $this->placeCategoryRepository = $placeCategoryRepo;
        $this->postRepository = $postRepository;
        $this->placeApplyRespository = $placeApplyRespository;
        $this->placeTransfomer = $placeTransfomer;
    }

    public function search(Request $request)
    {
        return [];
        // $ret = $this->placeRepository->search($request->input());
        // $this->placeTransfomer->setGeo($request->has('latlng'));

        // return $this->respondPaginate($ret, $this->placeTransfomer);
    }

    public function create(PlaceApplyRequest $request)
    {
        $request->merge([
            'apply_user_id' => $this->getCurrentUserId()
        ]);
        $ret = $this->placeApplyRespository->createModel($request->input());
        WeChatNotifyFacade::collectCredentials($request, config('wechat.scenes.approved'), $ret->id);

        if (!config('app.shouldApprove')) {
            Artisan::call('place:approve', ['--pid' => $ret->id]);
        }

        return $ret;
    }

    public function update(Request $request, $externalId)
    {
        // $this->validate($request, self::validateRule);
        // $place = $this->placeRepository->updatePlace($externalId, $request->all());

        // return $place;
    }

    public function all()
    {
        // return $this->placeRepository->getAll();
    }

    public function destroy($id)
    {
        // $place = $this->placeRepository->requireById($id);
        // $this->placeRepository->delete($place);

        // return $this->OK();
    }

    public function show(Request $request, $id)
    {
        // $place = $this->placeRepository->requireByExternalId($id);
        // $this->placeTransfomer->setPlaceSummary(true);
        // if ($this->getCurrentUser()) {
        //     $this->placeTransfomer->setUserSession($this->getCurrentUser());
        // }
        // return $this->respond(fractal($place, $this->placeTransfomer));
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        return parent::transformData($data, $transformer);
    }
}
