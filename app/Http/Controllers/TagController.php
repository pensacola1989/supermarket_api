<?php

namespace App\Http\Controllers;

use App\Exceptions\UserErrors;
use App\Http\Requests\TagRequest;
use App\Http\Transformers\TagTransformer;
use App\Services\Place\PlaceContract;
use App\Services\Tag\TagRepository;
use Illuminate\Http\Request;

class TagController extends Controller
{

    protected $tagRepository;

    protected $tagTransformer;

    protected $placeRepository;

    public function __construct(TagRepository $tagRepository, TagTransformer $tagTransformer, PlaceContract $placeContract)
    {
        $this->tagRepository = $tagRepository;
        $this->tagTransformer = $tagTransformer;
        $this->placeRepository = $placeContract;
    }

    public function getTags(Request $request)
    {
        $defaultSort = [
            'sortBy' => 'order',
            'sortType' => 'ASC',
        ];
        if (!$request->has('sortBy')) {
            $request->merge($defaultSort);
        }
        $tags = $this->tagRepository->search($request->input());

        return $this->respondPaginate($tags, $this->tagTransformer);
    }

    public function show($tagId)
    {
        $tag = $this->tagRepository->requireById($tagId);

        return $this->respond(fractal($tag, $this->tagTransformer)->parseIncludes('place'));
    }

    public function create(TagRequest $tagRequest, $placeId)
    {
        $alreadyExsit = $this->tagRepository->placeHasTag($tagRequest->place->id, $tagRequest->tag_name);
        if ($alreadyExsit) {
            throw UserErrors::TagAlreadyExist()->toException();
        }
        $ret = $this->tagRepository->createModel([
            'created_by_place_id' => $tagRequest->place->id,
            'tag_name' => $tagRequest->tag_name
        ]);
        $tag = $this->tagRepository->requireById($ret->id);

        return $this->respond(fractal($tag, $this->tagTransformer));
    }

    public function getPlaceTags(Request $request, $placeId)
    {
        $tags = $this->tagRepository->getTagsByPlaceId($request->place->id);

        return $this->respond(fractal($tags, $this->tagTransformer));
    }

    public function getManagePlaceTags(Request $request)
    {
        $tags = $this->tagRepository->getModel()->ofPlace($request->place->id)->get();

        return $this->respond(fractal($tags, $this->tagTransformer));
    }

    public function getPlaceTagsByExtId(Request $request, $placeId)
    {
        $place = $this->placeRepository->requireByExternalId($placeId);
        $tags = $this->tagRepository->getTagsByPlaceId($place->id);

        return $this->respond(fractal($tags, $this->tagTransformer));
    }

    public function update(TagRequest $tagRequest, $tagId)
    {
        $ret = $this->tagRepository->requireById($tagId)->update($tagRequest->input());

        return $this->respond([]);
    }

    public function destroy($tagId)
    {
        $tag = $this->tagRepository->requireById($tagId);
        $tag->delete();

        return $this->respond([]);
    }
}
