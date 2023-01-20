<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateVisitingCareForPwsdCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateVisitingCareForPwsdCalcSpecRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\CreateVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\EditVisitingCareForPwsdCalcSpecUseCase;
use UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase;

/**
 * 事業所算定情報（障害・重度訪問介護）コントローラー.
 */
final class VisitingCareForPwsdCalcSpecController extends Controller
{
    /**
     * 事業所算定情報（障害・重度訪問介護）を登録する.
     *
     * @param int $officeId
     * @param \UseCase\Office\CreateVisitingCareForPwsdCalcSpecUseCase $useCase
     * @param \App\Http\Requests\CreateVisitingCareForPwsdCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $officeId,
        CreateVisitingCareForPwsdCalcSpecUseCase $useCase,
        CreateVisitingCareForPwsdCalcSpecRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $officeId, $request->payload());
        return Response::created();
    }

    /**
     * 事業所算定情報（障害・重度訪問介護）を取得する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $officeId,
        int $id,
        LookupVisitingCareForPwsdCalcSpecUseCase $useCase,
        StaffRequest $request
    ): HttpResponse {
        $visitingCareForPwsdCalcSpec = $useCase->handle($request->context(), [Permission::viewInternalOffices()], $officeId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("VisitingCareForPwsdCalcSpec({$id}) not found");
            });
        return JsonResponse::ok(compact('visitingCareForPwsdCalcSpec'));
    }

    /**
     * 事業所算定情報（障害・重度訪問介護）を更新する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\EditVisitingCareForPwsdCalcSpecUseCase $useCase
     * @param \App\Http\Requests\UpdateVisitingCareForPwsdCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $officeId,
        int $id,
        EditVisitingCareForPwsdCalcSpecUseCase $useCase,
        UpdateVisitingCareForPwsdCalcSpecRequest $request
    ): HttpResponse {
        $visitingCareForPwsdCalcSpec = $useCase->handle($request->context(), $officeId, $id, $request->payload());
        return JsonResponse::ok(compact('visitingCareForPwsdCalcSpec'));
    }
}
