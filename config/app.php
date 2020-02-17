<?php

$ossBase = 'http://' . env('OSS_BUCK_NAME') . '.' . env('OSS_END_POINT', '');

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Shanghai'),
    'debug' => env('APP_DEBUG', false),
    'name' => env('APP_NAME', 'SM'),
    'shop_url' => env('SHOP_URL', ''),
    'domain_url' => env('DOMAIN_URL', ''),
    'share_url' => env('SHARE_URL', ''),
    'json_cdn' => env('JSON_CDN', ''),
    // 'http://' . env('OSS_BUCK_NAME') . '.' . env('OSS_END_POINT') . '/' . $fileName;
    'oss' => [
        'bucketName' => env('OSS_BUCK_NAME'),
        'endPoint' => env('OSS_END_POINT'),
        'fullUrlPrefix' => env('OSS_BUCK_NAME') . '.' . env('OSS_END_POINT')
    ],
    'hosts' => env('HOSTS', ''),
    'image_url_base' => $ossBase,
    'default_image' => [
        // 'url' => 'https://www.gravatar.com/avatar/sdfsdf?s=48&d=identicon&r=PG',
        'url' => 'https://www.gravatar.com/avatar',
        'params' => [
            's' => 48,
            'd' => 'identicon',
            'r' => 'PG',
        ],
    ],
    'directory' => [
        'image' => 'sm/data/images',
    ],
    'maxDistance' => env('MAX_LOAD_DISTANCE', 500)
];
