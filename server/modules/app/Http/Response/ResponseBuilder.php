<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Response Builder
 * @codeCoverageIgnore Responseのため、UnitTestでは実施しない
 */
trait ResponseBuilder
{
    /**
     * Create 200 Ok Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function ok($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_OK, $headers);
    }

    /**
     * Create 201 Created Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function created($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_CREATED, $headers);
    }

    /**
     * Create 202 accepted Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function accepted($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_ACCEPTED, $headers);
    }

    /**
     * Create 204 NoContent Response
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function noContent($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_NO_CONTENT, $headers);
    }

    /**
     * Create 400 BadRequest Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function badRequest($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_BAD_REQUEST, $headers);
    }

    /**
     * Create 401 Unauthorized Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function unauthorized($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_UNAUTHORIZED, $headers);
    }

    /**
     * Create 403 Forbidden Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function forbidden($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_FORBIDDEN, $headers);
    }

    /**
     * Create 404 NotFound Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function notFound($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_NOT_FOUND, $headers);
    }

    /**
     * Create 410 GONE Response.
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function gone($data = null, $headers = []): SymfonyResponse
    {
        return static::createInstance($data, SymfonyResponse::HTTP_GONE, $headers);
    }

    /**
     * Create Response Instance.
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    abstract protected static function createInstance($data, $status, $headers): SymfonyResponse;
}
