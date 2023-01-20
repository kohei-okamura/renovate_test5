<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FindLtcsAreaGradeRequest;
use App\Http\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\LtcsAreaGrade\FindLtcsAreaGradeUseCase;

/**
 * 介保地域区分コントローラー.
 */
final class LtcsAreaGradeController extends Controller
{
    /**
     * 介保域区分を検索する.
     *
     * @param \UseCase\LtcsAreaGrade\FindLtcsAreaGradeUseCase $useCase
     * @param \App\Http\Requests\FindLtcsAreaGradeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindLtcsAreaGradeUseCase $useCase, FindLtcsAreaGradeRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }
}
