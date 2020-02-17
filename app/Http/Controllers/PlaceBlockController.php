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
use App\Services\Place\PlaceBlockContract;
use App\Http\Transformers\PlaceBlockTransformer;
use App\Services\Account\UserContract;

class PlaceBlockController extends Controller
{

    protected $placeBlockRepository;

    protected $userRepository;

    protected $placeBlockTransformer;

    public function __construct(PlaceBlockContract $placeBlockContract, PlaceBlockTransformer $placeBlockTransformer, UserContract $userContract)
    {
        $this->placeBlockRepository = $placeBlockContract;
        $this->placeBlockTransformer = $placeBlockTransformer;
        $this->userRepository = $userContract;
    }

    public function search(Request $request)
    {
        // should exclude admin user
        $request->query->set('placeId', $request->place->id);
        // $request->query->set('adminId', $request->place->admin_id);
        $ret = $this->placeBlockRepository->search($request->query());

        return $this->respondPaginate($ret, $this->placeBlockTransformer);
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        return parent::transformData($data, $transformer);
    }

    public function create(Request $request, $userId)
    {
        $alreadyBlocked = $this->placeBlockRepository
            ->getModel()
            ->where([
                ['user_id', '=', $userId],
                ['place_id', '=', $request->place->id]
            ])->count();
        if ($alreadyBlocked) {
            throw UserErrors::CannotRepeatBlockAction()->toException();
        }
        $block = $this->placeBlockRepository->getNew([
            'user_id' => $userId,
            'place_id' => $request->place->id
        ]);
        $block->save();

        return  $this->Created();
    }

    public function update(Request $request, $externalId)
    { }

    public function all()
    { }

    public function destroy(Request $request, $userId)
    {
        // $user = $this->userRepository->requireByExternalId($userId);
        $place = $request->place;

        $this->placeBlockRepository->removeBlockUser($place->id, $userId);
    }

    public function show(Request $request, $placeId)
    { }
}
