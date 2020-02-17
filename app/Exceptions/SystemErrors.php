<?php

namespace App\Exceptions;

class SystemErrors
{
    public static function InternalServerError()
    {
        return new ApiError(ErrorLevel::$Error, "1000", "An unexpected server error happened, please contact developer to get help.", 500);
    }

    public static function TokenUnableRrefreshed($detail)
    {
        return new ApiError(ErrorLevel::$Error, "1001", "Not able to refresh Token: $detail", 401);
    }

    public static function PaywayServerError($detail)
    {
        return new ApiError(ErrorLevel::$Error, "1002", "Not able to visit payway: $detail", 500);
    }

    public static function WeChatAcessTokenError($detail)
    {
        return new ApiError(ErrorLevel::$Error, '1003', "WeChat access_token error: $detail", 500);
    }

    public static function HttpRequestError($detail)
    {
        return new ApiError(ErrorLevel::$Error, '1004', "Http request error: $detail", 500);
    }

    public static function MissingRequestDataError($detail)
    {
        return new ApiError(ErrorLevel::$Error, '1005', "Missing the request data error: $detail", 500);
    }

    public static function UserIsBlocked()
    {
        return new ApiError(ErrorLevel::$Error, '1006', "user has been blocked", 500);
    }

    public static function UserNotReigstered()
    {
        return new ApiError(ErrorLevel::$Error, '1007', "user has not registered", 400);
    }
}
