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
use UseCase\Billing\GetDwsBillingFileInfoUseCase;

/**
 * 障害福祉サービス：請求：ファイル コントローラ.
 */
class DwsBillingFileController extends Controller
{
    /**
     * 障害福祉サービス：請求：ファイル を取得する.
     *
     * @param int $dwsBillingId
     * @param string $token
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetDwsBillingFileInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $dwsBillingId,
        string $token,
        StaffRequest $request,
        GetDwsBillingFileInfoUseCase $useCase
    ): HttpResponse {
        $url = $useCase->handle($request->context(), $dwsBillingId, $token);

        return JsonResponse::ok(compact('url'));
    }
}
