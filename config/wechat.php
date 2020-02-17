<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/17/17
 * Time: 11:19 PM
 */

return [
    //    'appid' => 'wxcefd504e4a0863cd',
    //    'secret' => 'f5a57cbd8ad7cb32af45c040808aa1c1',
    //    'appid' => 'wx7e70a205e8aa200e',
    //    'secret' => '7933aacc64fe8b7919a9a771a90df450',
    'appid' => env('WECHAT_APPID', 'wx7e70a205e8aa200e'),
    'secret' => env('WECHAT_SECRET', '5480f1ab5e86229ce0217bf397def3be'),
    'code2session_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    'send_template_msg_url' => 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=ACCESS_TOKEN',
    'access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
    'template_message_url' => 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s',
    'subscribe_message_url' => 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=%s',
    'template_get' => 'https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token=%s',
    'headers' => [
        'formId' => 'X-MB-FormId',
        'openId' => 'X-MB-openId',
    ],
    'scenes' => [
        'approved' => 'approved',
        'comment' => 'comment',
        'reply' => 'reply'
    ],
    'scene' => [
        'approved' => [
            'templateId' => 'QJMeS1QmgrSrHACBz_p5V9Bx-L8_xnBWfU7A6WVRB9g',
            'page' => 'pages/board/board'
        ],
        'comment' => [
            'templateId' => 'ZTssZqIcUO3ofLdRn72HgYOr-d_0s6CfqEcpQw1_PHg',
            'page' => 'pages/notify/notify'
        ],
        'reply' => [
            'templateId' => '_0D_b7FGbJliOvZDo-GhpaLEcRyvIOT1KnGaene1qfs',
            'page' => 'pages/sub-comment/sub-comment'
        ]
    ],
];
