<?php

namespace App\Http\Transformers;

use App\Services\Account\User;
use App\Services\Place\Place;
use App\Services\Place\PlaceRepository;
use App\Services\Post\PostRepository;
use League\Fractal\TransformerAbstract;

// avatar: {
//     id: 3, type: "image/jpg", …
// attach_file_name: "http://ehe1989.oss-cn-hangzhou.aliyuncs.com/9f2f070828381f301a86a652a0014c086f06f0aa.jpg"
// created_at: null
// external_id: 123123123
// height: null
// id: 3
// type: "image/jpg"
// updated_at: null
// width: null
// }
// avatar_id: 3
// category_id: 1
// cover_id: 3
// created_at: null
// deleted_at: null
// desc: "姑苏城外寒山寺"
// external_id: 2608959009555456
// geo_hash: "wtw3gdchyjy4"
// id: 2
// lat: "41.26107000000000000"
// lng: "121.44451000000000000"
// name: "苏州平江路"
// updated_at: null
class PlaceTransfomer extends TransformerAbstract
{
    private $isPlaceSummary = false;

    private $shouldIncludeConfigOptions = false;

    private $shouldIncludeAdminSummary = false;

    private $latlng = false;

    private $userSession = null;

    protected $availableIncludes = [];

    protected $defaultIncludes = [
        'avatar',
        'cover',
    ];

    protected $postRepository;

    protected $placeRepository;

    private $attachmentTransformer;

    public function __construct(
        PostRepository $postRepository,
        PlaceRepository $placeRepository,
        AttachmentTransformer $attachmentTransformer
    ) {
        $this->postRepository = $postRepository;
        $this->placeRepository = $placeRepository;
        $this->attachmentTransformer = $attachmentTransformer;
    }

    public function setPlaceSummary(bool $isSummary)
    {
        $this->isPlaceSummary = $isSummary;

        return $this;
    }

    public function setIncludeAdminSummary(bool $tag)
    {
        $this->shouldIncludeAdminSummary = $tag;

        return $this;
    }

    public function setGeo($latlng)
    {
        $this->latlng = $latlng;

        return $this;
    }

    public function setUserSession(User $user)
    {
        $this->userSession = $user;

        return $this;
    }

    public function setIncludeConfigs(bool $shouldInclude)
    {
        $this->shouldIncludeConfigOptions = $shouldInclude;

        return $this;
    }

    public function transform(Place $place)
    {
        $placeBase = [
            'categoryId' => $place->category_id,
            'coverId' => $place->cover_id,
            'desc' => $place->desc,
            'externalId' => $place->external_id,
            'geoHash' => $place->geo_hash,
            'id' => $place->id,
            'lat' => $place->lat,
            'lng' => $place->lng,
            'name' => $place->name,
            'createdAt' => $place->created_at ? $place->created_at->toDateTimeString() : null,
            'mpId' => $place->mp_id,
            'adminId' => $place->admin_id,
            'isPrivate' => $place->is_private,
            'configs' => $place->configs,
            'canReply' => $this->getPlaceConfig($place, 1),
            'canPost' => $this->getPlaceConfig($place, 2),
            'canAnonymous' => $this->getPlaceConfig($place, 3),
        ];
        if ($this->isPlaceSummary) {
            $placeBase['postCount'] = $place->posts()->count();
            $placeBase['viewCount'] = $place->histories()->count();
            $placeBase['photos'] = fractal($this->placeRepository->getPhotoWall($place->id), $this->attachmentTransformer);
        }
        if ($this->userSession) {
            $placeBase['isUserSub'] = $this->placeRepository->isUserSubPlace($this->userSession->id, $place->id);
            $placeBase['isUserBlock'] = $this->placeRepository->userIsBlock($this->userSession->id, $place->id);
            $placeBase['isAdmin'] = $this->placeRepository->isUserAdmin($this->userSession->id, $place->id);
            $userApply = $place->userApply()->where('user_id', $this->userSession->id)->first();
            if ($userApply) {
                $placeBase['applyStatus'] = $userApply->status;
            }
        }

        if ($this->shouldIncludeConfigOptions) {
            $placeBase['configOptions'] = getPlaceDefaultConfigOptions();
        }

        if ($this->shouldIncludeAdminSummary) {
            $placeBase['summary']['applierCount'] = $place->userApply()->where('status', 0)->count();
            $placeBase['summary']['blocks'] = $place->blocks()->count();
            $placeBase['summary']['subscribes'] = $place->subscribes()->count();
        }

        if ($this->latlng) {
            list($lat, $lng) = explode(',', $this->latlng);
            $placeBase['distance'] = \MyHelper::getDistance([$lat, $lng], [$place->lat, $place->lng]);
        }

        return $placeBase;
    }

    public function includeAvatar(Place $place)
    {
        return $this->_parseAttachment($place->avatar);
    }

    public function includeCover(Place $place)
    {
        return $this->_parseAttachment($place->cover);
    }

    private function _parseAttachment($attachment)
    {
        if (!$attachment) {
            return null;
        }
        return $this->item($attachment, new AttachmentTransformer());
    }

    private function getPlaceConfig($place, $id)
    {
        // admin always true
        if ($this->userSession && $place->admin_id === $this->userSession->id) {
            return true;
        }

        return getPlaceConfigDirectlyValues($place->configs, $id);
    }
}
