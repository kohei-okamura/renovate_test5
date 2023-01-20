<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateHomeHelpServiceCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateHomeHelpServiceCalcSpecRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\CreateHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\EditHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase;

/**
 * 事業所算定情報（障害・居宅介護）コントローラー.
 */
final class HomeHelpServiceCalcSpecController extends Controller
{
    /**
     * 事業所算定情報（障害・居宅介護）を登録する.
     *
     * @param int $officeId
     * @param \UseCase\Office\CreateHomeHelpServiceCalcSpecUseCase $useCase
     * @param \App\Http\Requests\CreateHomeHelpServiceCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $officeId,
        CreateHomeHelpServiceCalcSpecUseCase $useCase,
        CreateHomeHelpServiceCalcSpecRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $officeId, $request->payload());
        return Response::created();
    }

    /**
     * 事業所算定情報（障害・居宅介護）を取得する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $officeId, int $id, LookupHomeHelpServiceCalcSpecUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $homeHelpServiceCalcSpec = $useCase->handle($request->context(), [Permission::viewInternalOffices()], $officeId, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("HomeHelpServiceCalcSpec({$id}) not found");
        });
        return JsonResponse::ok(compact('homeHelpServiceCalcSpec'));
    }

    /**
     * 事業所算定情報（障害・居宅介護）を更新する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\EditHomeHelpServiceCalcSpecUseCase $useCase
     * @param \App\Http\Requests\UpdateHomeHelpServiceCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $officeId,
        int $id,
        EditHomeHelpServiceCalcSpecUseCase $useCase,
        UpdateHomeHelpServiceCalcSpecRequest $request
    ): HttpResponse {
        $homeHelpServiceCalcSpec = $useCase->handle($request->context(), $officeId, $id, $request->payload());
        return JsonResponse::ok(compact('homeHelpServiceCalcSpec'));
    }
}
