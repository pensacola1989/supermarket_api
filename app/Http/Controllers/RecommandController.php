<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 11/3/17
 * Time: 3:13 PM
 */

namespace App\Http\Controllers;

use App\Http\Transformers\PlaceTransfomer;
use App\Services\Recommand\RecommandContract;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

class RecommandController extends Controller
{

    private $validateRule = [
        'recommand_place_id' => 'required|exists:places,id',
        'recommand_order' => 'digits_between:0,2',
    ];

    private $updateRule = [
        'recommand_order' => 'digits_between:0,2',
    ];

    private $placeTransformer;

    private $_recommandRepository;

    public function __construct(RecommandContract $recommandContract, PlaceTransfomer $placeTransformer)
    {
        $this->_recommandRepository = $recommandContract;
        $this->placeTransformer = $placeTransformer;
        // $this->middleware('wechat-auth', ['only' => ['search', 'show', 'all']]);
        // $this->middleware('auth:api', ['only' => ['search', 'show', 'all']]);
        // $this->middleware('auth:admin', ['only' => ['create', 'update', 'sort']]);
    }

    public function search(Request $request)
    {
        $request->merge(['sortBy' => 'recommand_order', 'sortType' => 'asc']);
        $recomamnds = $this->_recommandRepository->search($request->input());

        return $this->respondPaginate($recomamnds, $this->placeTransformer);
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        // $data = $data->pluck('Place');
        return parent::transformData($data, $transformer);
    }

    public function create(Request $request)
    {
        $this->customerValidate($request, $this->validateRule);
        $recommand = $this->_recommandRepository->getNew($request->input());
        $recommand->save();

        return $recommand;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, $this->updateRule);
        $recommand = $this->_recommandRepository->requireById($id);
        $recommand->update($request->input());

        return $recommand;
    }

    public function sort($id, $replaceId)
    {
        $record = $this->_recommandRepository->requireById($id);
        $replaceRecord = $this->_recommandRepository->requireById($replaceId);
        $temp = $replaceRecord->recommand_order;
        $replaceRecord->recommand_order = $record->recommand_order;
        $record->recommand_order = $temp;

        $record->save();
        $replaceRecord->save();

        return $this->OK();
    }

    public function all()
    {
        return $this->_recommandRepository->getAll();
    }

    public function destroy($id)
    {
        $model = $this->_recommandRepository->requireById($id);
        $this->_recommandRepository->delete($model);

        return $this->OK();
    }

    public function show($id)
    {
        $model = $this->_recommandRepository->requireByExternalId($id);

        return $model;
    }
}
