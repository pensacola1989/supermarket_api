<?php

namespace App\Exceptions;

use Anik\Form\ValidationException as FormValidationException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // if (app()->environment('local')) {
        //     return parent::render($request, $exception);
        // }
        $apiError = null;
        $statusCode = 0;
        $responseData = null;
        $headers = [];

        if (config('app.debug')) {
            $debug_id = uniqid();
            Log::debug($debug_id, [
                'LOG_ID' => $debug_id,
                'IP_ADDRESS' => $request->ip(),
                'REQUEST_URL' => $request->fullUrl(),
                'AUTHORIZATION' => $request->header('Authorization'),
                'REQUEST_METHOD' => $request->method(),
                'PARAMETERS' => $request->all(),
            ]);
            $headers = array_merge($headers, ['X-' . config('app.name') . '-DebugId' => $debug_id]);
        }
        if ($exception instanceof FormValidationException) {
            $responseData = $exception->getResponse();
            $apiError = UserErrors::FormValidationException(json_encode($responseData), $exception->getStatusCode());
            $exception = $apiError->toException();
        } elseif ($exception instanceof UnauthorizedHttpException) {
            $previousException = $exception->getPrevious();
            if ($previousException instanceof TokenInvalidException) {
                $exception = UserErrors::TokenInvalidException($previousException->getMessage(), 401)->toException($previousException);
            } else if ($previousException instanceof TokenExpiredException) {
                $refreshKey = $this->_refreshKey();
                $headers = array_merge($headers, ['X-' . config('app.name') . '-Refresh-Key' => $refreshKey]);
                $exception = UserErrors::TokenExpiredException($previousException->getMessage(), 401)->toException($previousException);
            } else {
                $exception = UserErrors::UnauthorizedHttpException($exception->getMessage(), $exception->getStatusCode())->toException($exception);
            }
        }
        if ($exception instanceof ApiException) {
            Log::warning($exception->getMessage());
            $apiError = $exception->getError();
        } else {
            $exceptionName = (new \ReflectionClass($exception))->getShortName();
            $exceptionErrors = self::_getExceptionErrors();
            if (array_key_exists($exceptionName, $exceptionErrors)) {
                Log::error($exception->getMessage());
                $apiError = $exceptionErrors[$exceptionName];
            } else {
                $apiError = SystemErrors::InternalServerError();
                Log::critical($exception);
                if (app()->environment('local')) {
                    return parent::render($request, $exception);
                }
            }
        }

        $response = response()->json($responseData, $apiError->getStatusCode(), $headers);
        $response->header('X-' . config('app.name') . '-ErrorCode', $apiError->getErrorCode());
        $response->header('X-' . config('app.name') . '-ErrorMessage', $apiError->getErrorMessage());

        return $response;
        // $errResponse = [];

        // if ($exception instanceof EntityNotFoundException) {
        //     return response([
        //         'errCode' => 101,
        //         'msg' => 'Entity Not Found',
        //     ]);
        // }
        // if ($exception instanceof NotAllowException) {
        //     return response([
        //         'errCode' => $exception->getStatusCode(),
        //         'msg' => $exception->getMessage(),
        //     ]);
        // }
        // if ($exception instanceof ValidationException) {
        //     return response([
        //         'errCode' => 201,
        //         'msg' => $exception->validator->errors(),
        //     ]);
        // }
        // if ($exception instanceof JWTException) {
        //     return response([
        //         'errCode' => 401,
        //         'msg' => 'token_absent',
        //     ]);
        // }
        // if ($exception instanceof TokenInvalidException) {
        //     return response([
        //         'errCode' => 401,
        //         'msg' => 'token_invlid',
        //     ]);
        // }
        // if ($exception instanceof QueryException) {
        //     $error_code = $exception->errorInfo[1];
        //     if ($error_code == 1062) {
        //         return response([
        //             'errCode' => 1002,
        //             'msg' => '重复记录',
        //         ]);
        //     }
        // }
        // if ($exception instanceof TokenExpiredException) {
        //     $token = JWTAuth::getToken();
        //     if (!$token) {
        //         return response(['errCode' => 401, 'msg' => 'token not provided']);
        //     }
        //     try {
        //         $refreshedToken = JWTAuth::refresh($token);
        //         return response(['errCode' => 401, 'refreshedToken' => $refreshedToken, 'msg' => 'token refreshed!']);
        //     } catch (JWTException $e) {
        //         return response(['errCode' => 401, 'msg' => 'Not able to refresh Token']);
        //     }
        // }

        // return parent::render($request, $e);
    }

    private function _refreshKey()
    {
        try {
            $sKey = app('request')->headers->get("X-" . config('app.name') . "-Key");
            $unionId = app('request')->headers->get("X-" . config('app.name') . "-UnionId");

            return generateAuthkey($unionId);
            // $jwt  = app()->make(JWTAuth::class);
            // $token = $jwt->getToken();

            // return $jwt->refresh($token);
        } catch (JWTException $e) {
            throw SystemErrors::TokenUnableRrefreshed($e->getMessage())->toException($e);
        }
    }

    private static function _getExceptionErrors()
    {
        return [
            'NotFoundHttpException' => UserErrors::NotFoundHttpException(),
            'MethodNotAllowedHttpException' => UserErrors::NotFoundHttpException(),
            'UnauthorizedHttpException' => UserErrors::UnauthorizedHttpException('Unauthorized!'),
        ];
    }
}
