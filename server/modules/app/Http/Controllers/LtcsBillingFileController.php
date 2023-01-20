<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\GetLtcsBillingFileInfoUseCase;

/**
 * 介護保険サービス：請求：ファイル コントローラ.
 */
class LtcsBillingFileController extends Controller
{
    /**
     * 障害福祉サービス：請求：ファイル を取得する.
     *
     * @param int $id
     * @param string $token
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetLtcsBillingFileInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $id,
        string $token,
        StaffRequest $request,
        GetLtcsBillingFileInfoUseCase $useCase
    ): HttpResponse {
        $url = $useCase->handle($request->context(), $id, $token);

        return JsonResponse::ok(compact('url'));
    }
}
