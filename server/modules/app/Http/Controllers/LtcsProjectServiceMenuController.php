<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Project\GetLtcsProjectServiceMenuListUseCase;

/**
 * 介護保険サービス：計画：サービス内容コントローラー.
 */
final class LtcsProjectServiceMenuController extends Controller
{
    /**
     * 介護保険サービス：計画：サービス内容を一覧取得する.
     *
     * @param \UseCase\Project\GetLtcsProjectServiceMenuListUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(GetLtcsProjectServiceMenuListUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $response = $useCase->handle($request->context(), !empty($request->all));
        return JsonResponse::ok($response);
    }
}
