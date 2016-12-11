<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof BadRequestException) {
            return response()->json(['msg' => $e->getMessage()], $e->getCode());
        } else if ($e instanceof APIException) {
            return response()->json(['msg' => $e->getMessage()], $e->getCode());
        } else if ($e instanceof ModelNotFoundException) {
            return response()->json(['msg' => '资源不存在'], 404);
        } else if ($e instanceof NotFoundHttpException){
            return response()->json(['msg' => '链接不存在'], 404);
        } else if ($e instanceof AuthenticationException) {
            return response()->json(['msg' => '未授权'], 401);
        } else if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json(['msg' => '无效的访问方式'], 404);
        }

        return parent::render($request, $e);
    }
}
