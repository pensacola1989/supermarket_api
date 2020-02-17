<?php

namespace App\Services\Core;

use Anik\Form\FormRequest;

abstract class BaseRequest extends FormRequest
{
    protected $messageFormat = [
        'between' => ':attribute 必须在:min - :max 之间.',
        'required' => ':attribute 必填项.',
        'integer' => ':attribute 必须是整数.',
        'required_without_all' => '当 :values为空时，:attribute 不为空',
        'exists' => ':attribute 不存在.',
        'min' => ':attribute 必须大于等于:min',
        'min' => ':attribute 必须小于等于:max',
        'unique' => ':attribute 已存在'
    ];



    protected function messages()
    {
        return $this->messageFormat;
    }

    public function getCurrentUser()
    {
        return app()->make('request')->user();
    }
}
