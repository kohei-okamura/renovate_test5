<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationRequest;
use App\Http\Response\Response;
use Infrastructure\Office\Office;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * システムステータスコントローラ.
 */
final class StatusController extends Controller
{
    /**
     * システムステータスを取得する.
     *
     * @param \App\Http\Requests\OrganizationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(OrganizationRequest $request): HttpResponse
    {
        // DB(RDS)アクセスのチェック
        Office::orderBy('id')->limit(1)->get();
        // Redis (ElasticCache) アクセスのチェック
        app('redis')->command('ping');

        return Response::ok();
    }
}
