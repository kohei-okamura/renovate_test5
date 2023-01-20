<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Response\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\ForbiddenException;
use Lib\Exceptions\InvalidMultipleOperationException;
use Lib\Exceptions\NetworkIOException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;
use Lib\Exceptions\UnauthorizedException;
use Lib\Logging;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Handler for Exception.
 *
 * @codeCoverageIgnore HTTPレスポンスを返す処理はUnitTest除外.
 */
class Handler extends ExceptionHandler
{
    use Logging;

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
     * @param \Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($exception instanceof UnauthorizedException) {
            // No Logs
        } elseif ($exception instanceof InvalidMultipleOperationException) {
            $this->logger()->warning($exception->getMessage());
        } elseif ($exception instanceof ForbiddenException) {
            $this->logger()->info($exception->getMessage());
        } elseif ($exception instanceof NotFoundException) {
            $this->logger()->warning($exception->getMessage());
        } elseif ($exception instanceof TokenExpiredException) {
            $this->logger()->info($exception->getMessage());
        } elseif ($exception instanceof NetworkIOException) {
            $this->logger()->critical('ネットワークエラー: ' . $exception->getMessage(), ['exception' => $exception]);
        } elseif ($exception instanceof FileIOException) {
            $this->logger()->critical('ファイルIOエラー: ' . $exception->getMessage(), ['exception' => $exception]);
        } else {
            parent::report($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedException) {
            return parent::render($request, new HttpException(IlluminateResponse::HTTP_UNAUTHORIZED, $exception->getMessage()));
        } elseif ($exception instanceof InvalidMultipleOperationException) {
            return Response::badRequest();
        } elseif ($exception instanceof ForbiddenException) {
            return Response::forbidden();
        } elseif ($exception instanceof NotFoundException) {
            return Response::notFound();
        } elseif ($exception instanceof TokenExpiredException) {
            return Response::gone();
        } elseif ($exception instanceof NetworkIOException) {
            return parent::render($request, $exception);
        } elseif ($exception instanceof FileIOException) {
            return parent::render($request, $exception);
        } else {
            return parent::render($request, $exception);
        }
    }
}
