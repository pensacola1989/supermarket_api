<?php


/**
 * reated by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 11:40 PM
 */

namespace App\Http\Controllers;


use App\Services\Place\PlaceCategoryContract;
use Illuminate\Http\Request;
use App\Http\Transformers\PlaceCategoryTransformer;

class PlaceCategoryController extends Controller
{
    private $validateRule = [
        'name' => 'required|between:1,30'
    ];


    private $placeCategoryRepository;

    protected $placeCategoryTransformer;

    public function __construct(PlaceCategoryContract $placeCategoryContract, PlaceCategoryTransformer $placeCategoryTransformer)
    {
        $this->placeCategoryRepository = $placeCategoryContract;
        $this->placeCategoryTransformer = $placeCategoryTransformer;
    }

    public function search(Request $request)
    {
        $ret =  $this->placeCategoryRepository->search($request->input());
        return $this->respondPaginate($ret, $this->placeCategoryTransformer);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, $this->validateRule);
        $placeCategory = $this->placeCategoryRepository->createPlaceCategory($request->input());

        return $placeCategory;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRule);
        $placeCategory = $this->placeCategoryRepository->updateModel($id, $request->all());

        return $placeCategory;
    }

    public function all()
    {
        return $this->placeCategoryRepository->getAll();
    }

    public function destroy($id)
    {
        $model = $this->placeCategoryRepository->requireById($id);
        $this->placeCategoryRepository->delete($model);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->placeCategoryRepository->requireByExternalId($id);

        return $model;
    }
}
