<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FindPermissionGroupRequest;
use App\Http\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Permission\FindPermissionGroupUseCase;

/**
 * 権限グループコントローラー.
 */
final class PermissionGroupController extends Controller
{
    /**
     * 権限グループの一覧取得をする.
     *
     * @param \UseCase\Permission\FindPermissionGroupUseCase $useCase
     * @param \App\Http\Requests\FindPermissionGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindPermissionGroupUseCase $useCase, FindPermissionGroupRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }
}
