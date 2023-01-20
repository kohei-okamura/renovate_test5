<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FindDwsAreaGradeRequest;
use App\Http\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\DwsAreaGrade\FindDwsAreaGradeUseCase;

/**
 * 障害福祉サービス地域区分コントローラー.
 */
final class DwsAreaGradeController extends Controller
{
    /**
     * 障害福祉サービス地域区分を検索する.
     *
     * @param \UseCase\DwsAreaGrade\FindDwsAreaGradeUseCase $useCase
     * @param \App\Http\Requests\FindDwsAreaGradeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindDwsAreaGradeUseCase $useCase, FindDwsAreaGradeRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }
}
