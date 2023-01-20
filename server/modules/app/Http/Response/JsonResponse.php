<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Response;

use Illuminate\Http\JsonResponse as BaseJsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * JSON Response builder.
 */
final class JsonResponse
{
    use ResponseBuilder;

    /** {@inheritdoc} */
    protected static function createInstance($data, $status, $headers): SymfonyResponse
    {
        $response = new BaseJsonResponse($data, $status, $headers);
        return $response->setEncodingOptions(\JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
    }
}
