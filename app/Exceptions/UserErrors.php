<?php

namespace App\Exceptions;

class UserErrors
{
    public static function EntityNotFound($entityName, $id)
    {
        return new ApiError(ErrorLevel::$Error, "2001", "The entity $entityName of id: $id can't be found", 404);
    }

    public static function FormValidationException($message, $statusCode)
    {
        return new ApiError(ErrorLevel::$Error, "2002", $message, $statusCode);
    }

    public static function NotFoundHttpException()
    {
        return new ApiError(ErrorLevel::$Error, "2003", 'The route request is not existed.', 404);
    }

    public static function UnauthorizedHttpException($message, $statusCode = 401)
    {
        return new ApiError(ErrorLevel::$Error, "2004", $message, $statusCode);
    }

    public static function TokenInvalidException($message, $statusCode)
    {
        return new ApiError(ErrorLevel::$Error, "2005", $message, $statusCode);
    }

    public static function TokenExpiredException($message, $statusCode)
    {
        return new ApiError(ErrorLevel::$Error, "2006", $message, $statusCode);
    }

    public static function NoPermission()
    {
        return new ApiError(ErrorLevel::$Error, "2029", "You don't have permission to operation others resource.", 403);
    }


    public static function CannotDuplicateRefund()
    {
        return new ApiError(ErrorLevel::$Error, "2033", "You can not refund duplicate.", 400);
    }

    public static function VerifyCloudAppSignatureFailed()
    {
        return new ApiError(ErrorLevel::$Error, "2034", "Can not verify cloud app header.", 401);
    }


    public static function UserPostWithTagReachLimit($limit)
    {
        return new ApiError(ErrorLevel::$Error, "2026", "User can only post with $limit tags", 400);
    }

    public static function userIsBlockedForThisPlace()
    {
        return new ApiError(ErrorLevel::$Error, '2013', "User is blocked for this palce", 403);
    }

    public static function UserCannotRepeatDoThisAction($type)
    {
        return new ApiError(ErrorLevel::$Error, '2014', "User can not repeat doing this action with $type", 400);
    }

    public static function CannotRepeatBlockAction()
    {
        return new ApiError(ErrorLevel::$Error, '2015', "User has been blocked, please do not repeat this action", 400);
    }

    public static function youAreBlockedByThisUser()
    {
        return new ApiError(ErrorLevel::$Error, '2016', "You have been blocked by this user", 400);
    }

    public static function youCannotBlockYouSelf()
    {
        return new ApiError(ErrorLevel::$Error, '2017', "You can not block you self", 400);
    }

    public static function MpLinkIsNotValid()
    {
        return new ApiError(ErrorLevel::$Error, '2018', "MP link you provided is not valid", 400);
    }


    public static function TagAlreadyExist()
    {
        return new ApiError(ErrorLevel::$Error, '2019', "You can not create same tag", 400);
    }

    public static function youHaveBeenBlockedByApp()
    {
        return new ApiError(ErrorLevel::$Error, '2020', "You have been blocked by App", 403);
    }
}
