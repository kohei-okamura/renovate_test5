<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Response;

use Illuminate\Http\Response as BaseResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Response.
 * @codeCoverageIgnore Responseのため、UnitTestでは実施しない
 */
final class Response
{
    use ResponseBuilder;

    private const STREAM_READ_BUFFER_SIZE = 1024;

    /** {@inheritdoc} */
    protected static function createInstance($data, $status, $headers): SymfonyResponse
    {
        return is_resource($data)
            ? static::createStreamResponse($data, $status, $headers)
            : new BaseResponse($data, $status, $headers);
    }

    /**
     * ストリームレスポンスを作成する.
     *
     * @param resource $resource
     * @param int $status
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private static function createStreamResponse($resource, $status, $headers)
    {
        $f = function () use ($resource) {
            while (!feof($resource)) {
                echo fread($resource, self::STREAM_READ_BUFFER_SIZE);
            }
        };
        return new StreamedResponse($f, $status, $headers);
    }
}
