<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Exceptions;

use crmeb\exceptions\AdminException;
use crmeb\exceptions\ApiException;
use crmeb\exceptions\ApiRequestException;
use crmeb\exceptions\AuthException;
use crmeb\exceptions\EntException;
use crmeb\exceptions\HttpServiceExceptions;
use crmeb\exceptions\ServicesException;
use crmeb\exceptions\UploadException;
use crmeb\exceptions\WebOfficeException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Predis\Connection\ConnectionException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ValidationException::class,
        WebOfficeException::class,
        HttpServiceExceptions::class,
        ApiException::class,
        EntException::class,
        AdminException::class,
        ApiRequestException::class,
        ServicesException::class,
        UploadException::class,
        JWTException::class,
        AuthException::class,
        ConnectionException::class,
        AuthenticationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->reportable(function (\Throwable $e) {});
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Throwable
     */
    public function render($request, \Throwable $e)
    {
        $debug = env('APP_DEBUG', false);

        $data = $debug ? [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTrace(),
            //            'previous' => $e->getPrevious(),
        ] : [];
        if ($e instanceof QueryException
            || $e instanceof ModelNotFoundException
            || $e instanceof MethodNotAllowedHttpException
            || $e instanceof RouteNotFoundException
            || $e instanceof ValidationException
            || $e instanceof \ReflectionException
            || $e instanceof \BadMethodCallException
            || $e instanceof UnauthorizedHttpException
            || $e instanceof ApiException
            || $e instanceof EntryNotFoundException
        ) {
            return $this->response(400, $e->getMessage() ?: '系统开小差了', $data);
        }
        if ($e instanceof HttpServiceExceptions
            || $e instanceof UploadException
            || $e instanceof ApiRequestException
            || $e instanceof AdminException
            || $e instanceof ServicesException
            || $e instanceof ConnectionException
            || $e instanceof AuthException
            || $e instanceof EntException
        ) {
            return $this->response($e->getCode() ?: 400, $e->getMessage(), $data);
        }
        if (
            $e instanceof AuthorizationException
            || $e instanceof JWTException
        ) {
            return $this->response($e->getCode() ?: 403, $e->getMessage());
        }
        if (
            $e instanceof AuthenticationException
        ) {
            return $this->response(410003, $e->getMessage());
        }
        if ($e instanceof WebOfficeException) {
            return Response::json(['code' => 40007, 'details' => $e->getMessage(), 'message' => 'CustomMsg']);
        }
        if ($request->ajax()) {
            return $this->response($e->getCode() ?: 400, $e->getMessage(), $data);
        }
        if ($e instanceof \Exception) {
            return $this->response($e->getCode(), $e->getMessage() ?: '系统开小差了', $data);
        }
        // 添加自定义异常处理机制
        $this->report($e);
        if ($debug) {
            return parent::render($request, $e);
        }
        return app('json')->httpStatus(400)->fail('I\'m sorry, the system is out of order');
    }

    /**
     * 创建 response.
     * @param array $data
     * @param mixed $code
     * @param mixed $msg
     * @return mixed
     */
    protected function response($code, $msg, $data = [])
    {
        return app('json')->create(collect(['status' => $code, 'message' => $msg, 'data' => $data]));
    }
}
