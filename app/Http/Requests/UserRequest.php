<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/15/17
 * Time: 12:28 AM
 */
class UserRequest extends Request
{
    protected $rule= [
        'POST' => [
            'name' => 'required_without_all:nick_name|between:1,20',
            'nick_name' => 'required_without_all:name',
            'mobile' => 'alpha_num'
        ]
    ];

    public function validate($controller, Request $request)
    {
        $rule = $this->rule[$request->method()];
        $controller->validate($request, $rule);
    }
}