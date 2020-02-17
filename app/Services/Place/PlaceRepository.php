<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:09 PM
 */

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityRepository;
use Illuminate\Support\Facades\DB;
use MyHelper;

class PlaceRepository extends EntityRepository implements PlaceContract
{
    private $categoryModel;

    private $attachmentModel;

    private $placeBlock;

    private $placeSubApply;

    public function __construct(Place $model, PlaceCategories $categories, Attachment $attachment, PlaceBlock $placeBlock, PlaceSubApply $placeSubApply)
    {
        $this->model = $model;
        $this->categoryModel = $categories;
        $this->attachmentModel = $attachment;
        $this->placeBlock = $placeBlock;
        $this->placeSubApply = $placeSubApply;
    }

    public function createPlace($attribute)
    {
        $category = $this->categoryModel->find($attribute['category_id']);
        $ret = $category->Places()->create($attribute);

        return $this->getById($ret->id);
    }

    public function updatePlace($externalId, $attribute)
    {
        $category = $this->categoryModel->find($attribute['category_id']);
        $place = $this->getByExternalId($externalId);
        $place->update($attribute);
        $place->Category()->associate($category);
        $place->save();

        return $this->getById($place->id);
    }

    protected function constructQuery($criteria)
    {
        $query = $this->model;

        if (isset($criteria['kw']) && $criteria['kw'] != '') {
            $query = $query->where('name', 'like', '%' . $criteria['kw'] . '%');
        }

        if (isset($criteria['is_hot'])) {
            $query = $query->withCount('posts');
        }
        if (isset($criteria['latlng'])) {
            $geoHash = MyHelper::convertGeoToHash($criteria['latlng'], 4);
            $query = $query->where('geo_hash', 'like', '%' . $geoHash . '%')
                ->whereNotNull('lat')
                ->whereNotNull('lng');
        }
        if (isset($criteria['recommand'])) {
            $query = $query->has('recommand')->select('places.*');
        }
        if (isset($criteria['adminByMe']) && isset($criteria['currentUserId'])) {
            $query = $query->where('admin_id', $criteria['currentUserId']);
        } else {
            $query = $query->where('is_private', 0);
        }


        return $query;
    }

    protected function includeForQuery($query)
    {
        $query = $query->with(['Category', 'avatar']);

        return $query;
    }

    protected function loadRelated($entity)
    {
        $entity->load(['avatar', 'cover', 'Category']);
        // TODO: Implement loadRelated() method.
    }

    public function isUserSubPlace($userId, $placeId)
    {
        $count = $this->requireById($placeId)
            ->subscribes()
            ->where('sub_user_id', $userId)
            ->count();

        return $count > 0;
    }

    public function subscribePlace($userId, $placeId)
    {
        $place = $this->requireById($placeId);
        $ret = $place->subscribes()->create([
            'sub_user_id' => $userId,
        ]);

        return $ret;
    }

    public function unSubscribePlace($userId, $placeId)
    {
        $place = $this->getById($placeId);

        return $place->subscribes()->where('sub_user_id', $userId)->delete();
    }


    public function getPhotoWall($placeId, $pageSize = 5)
    {
        return $this->attachmentModel->whereHas('Post', function ($query) use ($placeId) {
            return $query
                ->where('place_id', $placeId)
                ->has('Photos', '>', 0);
        })
            ->take($pageSize)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function userIsBlock($userId, $placeId)
    {
        return $this->placeBlock->where('user_id', $userId)->where('place_id', $placeId)->count() > 0;
    }

    public function userHasApplied($userId, $placeId)
    {
        return $this->placeSubApply
            ->where([
                ['user_id', '=', $userId],
                ['place_id', '=', $placeId]
            ])->count() > 0;
    }

    public function addPlaceSubApply($userId, $placeId)
    {
        return $this->placeSubApply->create([
            'user_id' => $userId,
            'place_id' => $placeId
        ]);
    }

    public function userNeedApply($userId, $placeId)
    {
        $place =  $this->requireById($placeId);
        $userApply = $place->userApply()->where('user_id', $userId)->first();

        return $userApply && $userApply->status !== 1;
    }

    public function approvePlaceSubApply($userId, $placeId)
    {
        return $this->placeSubApply
            ->where([
                ['user_id', '=', $userId],
                ['place_id', '=', $placeId]
            ])
            ->update(['status' => 1]);
    }

    public function isUserAdmin($userId, $placeId)
    {
        $place = $this->requireById($placeId);

        return $place->admin_id === $userId;
    }

    public function getPlaceDefaultConfigOptions()
    {
        // $configOptions = getPlaceDefaultConfigOptions();
        // $defaultConfigs = collect($configOptions)
        //     ->map(function ($option) {
        //         return [
        //             'configId' => $option->id,
        //             'configValue' => $option->type === 'bool' ? intval($option->default_value) : $option->default_value
        //         ];
        //     });

        // return array_merge(['defaultConfigs' => $defaultConfigs], getPlaceConfigValues($defaultConfigs));
        $configOptions = getPlaceDefaultConfigOptions();
        return collect($configOptions)->map(function ($option) {
            return [
                'configId' => intval($option->id),
                'configValue' => $option->type === 'bool' ? intval($option->default_value) : $option->default_value
            ];
        });

        // [...{configId: configId, configValue: configValue}]
    }

    protected function constructOrderBys(&$criteria, $query)
    {
        if (isset($criteria['recommand'])) {
            $query = $query
                ->join('recommands', 'recommands.recommand_place_id', 'places.id')
                ->orderBy('recommands.recommand_order');

            $criteria['sortBy'] = 'places.id';
        }
        return $query;
    }
}
