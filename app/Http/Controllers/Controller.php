<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Lumen\Routing\Controller as BaseController;
use League\Fractal\TransformerAbstract;

class Controller extends BaseController
{
    /**
     * http status CREATED
     * @param null $content
     * @return Response
     */
    public function Created($content = null)
    {
        return response()->json($content, 201);
    }

    public function OK($content = null)
    {
        return response()->json($content, 200);
    }

    public function BadRequest($content = null)
    {
        return response()->json($content, 400);
    }

    public function InternalError($content = null)
    {
        return response()->json($content, 500);
    }

    protected $messageFormat = [
        'between' => ':attribute 必须在:min - :max 之间.',
        'required' => ':attribute 必填项.',
        'required_without_all' => '当 :values为空时，:attribute 不为空',
        'exists' => ':attribute 不存在.',

    ];

    protected function customerValidate(Request $request, $rule)
    {
        $this->validate($request, $rule, $this->messageFormat);
    }

    protected $statusCode = 200;

    protected function getStatusCode()
    {
        return $this->statusCode;
    }

    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    protected function paginate(LengthAwarePaginator $paginator, TransformerAbstract $transformer, $reverse)
    {
        $items = $paginator->items();
        return [
            'paginator' => [
                'totalItems' => $paginator->total(),
                'pageSize' => intval($paginator->perPage()),
                'pageCount' => ceil($paginator->total() / $paginator->perPage()),
                'currentPage' => $paginator->currentPage(),
            ],
            'list' => $this->transformData($items, $transformer),
        ];
    }

    protected function transformData($data, TransformerAbstract $transformer)
    {
        return fractal($data, $transformer)->toArray();
    }

    /**
     * 配置上拉加载和下拉刷新
     * @param $items
     * @return array
     */
    protected function _generateLoadRange(&$items)
    {
        $maxId = collect($items)->min('id');
        $sinceId = collect($items)->max('id');

        return [$sinceId, $maxId];
    }

    protected function _generateLoadTimeRange(&$items)
    {
        $maxTime = collect($items)->min('updatedAt');
        $sinceTime = collect($items)->max('updatedAt');

        return [$sinceTime, $maxTime];
    }


    public function respondPaginate(LengthAwarePaginator $paginator, TransformerAbstract $transformer = null, $reverse = false, $headers = [])
    {
        return $this->respond($this->paginate($paginator, $transformer, $reverse), $headers);
    }

    public function respond($data, $headers = [])
    {
        $response = response()->json($data, $this->getStatusCode(), $headers);
        $response->header('X-' . config('app.name') . '-ErrorCode', 0);
        return $response;
    }

    public function respondTimeLine(LengthAwarePaginator $paginator, TransformerAbstract $tranformer, $headers = [])
    {
        $data = $this->paginate($paginator, $tranformer, null);
        list($data['sinceId'], $data['maxId']) = $this->_generateLoadRange($data['list']);
        $response = response()->json($data, $this->getStatusCode(), $headers);
        $response->header('X-' . config('app.name') . '-ErrorCode', 0);

        return $response;
    }

    /**
     * 时间相关
     *
     * @param LengthAwarePaginator $paginator
     * @param TransformerAbstract $tranformer
     * @param array $headers
     * @return void
     */
    public function respondByActiveTimeLine(LengthAwarePaginator $paginator, TransformerAbstract $tranformer, $headers = [])
    {
        $data = $this->paginate($paginator, $tranformer, null);
        list($data['sinceTime'], $data['maxTime']) = $this->_generateLoadTimeRange($data['list']);
        $response = response()->json($data, $this->getStatusCode(), $headers);
        $response->header('X-' . config('app.name') . '-ErrorCode', 0);

        return $response;
    }

    public function getCurrentUser()
    {
        return app('request')->user();
    }

    public function getCurrentUserId()
    {
        $user = $this->getCurrentUser();
        return $user ? $user->id : null;
    }
}
