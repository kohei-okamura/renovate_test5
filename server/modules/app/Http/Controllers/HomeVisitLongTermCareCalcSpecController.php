<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateHomeVisitLongTermCareCalcSpecRequest;
use App\Http\Requests\GetHomeVisitLongTermCareCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateHomeVisitLongTermCareCalcSpecRequest;
use App\Http\Response\JsonResponse;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\CreateHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\EditHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase;
use UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase;

/**
 * 事業所算定情報（介保・訪問介護）コントローラー.
 */
final class HomeVisitLongTermCareCalcSpecController extends Controller
{
    /**
     * 事業所算定情報（介保・訪問介護）を登録する.
     *
     * @param int $officeId
     * @param \UseCase\Office\CreateHomeVisitLongTermCareCalcSpecUseCase $useCase
     * @param \App\Http\Requests\CreateHomeVisitLongTermCareCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(int $officeId, CreateHomeVisitLongTermCareCalcSpecUseCase $useCase, CreateHomeVisitLongTermCareCalcSpecRequest $request): HttpResponse
    {
        return JsonResponse::created($useCase->handle($request->context(), $officeId, $request->payload()));
    }

    /**
     * 事業所算定情報（介保・訪問介護）を取得する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $officeId, int $id, LookupHomeVisitLongTermCareCalcSpecUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $homeVisitLongTermCareCalcSpec = $useCase->handle($request->context(), [Permission::viewInternalOffices()], $officeId, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("HomeVisitLongTermCareCalcSpec({$id}) not found");
        });
        return JsonResponse::ok(compact('homeVisitLongTermCareCalcSpec'));
    }

    /**
     * サービス提供年月を指定して事業所算定情報（介保・訪問介護）を取得する.
     *
     * @param int $officeId
     * @param \UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase $useCase
     * @param \App\Http\Requests\GetHomeVisitLongTermCareCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function identify(
        int $officeId,
        GetHomeVisitLongTermCareCalcSpecUseCase $useCase,
        GetHomeVisitLongTermCareCalcSpecRequest $request
    ): HttpResponse {
        $providedIn = $request->payload()['providedIn'];
        $homeVisitLongTermCareCalcSpec = $useCase
            ->handle(
                $request->context(),
                [Permission::viewInternalOffices()],
                $officeId,
                $providedIn
            )
            ->getOrElse(function () use ($officeId, $providedIn): void {
                throw new NotFoundException("HomeVisitLongTermCareCalcSpec(officeId={$officeId}, providedIn={$providedIn->format('Y-m')}) not found");
            });
        return JsonResponse::ok(compact('homeVisitLongTermCareCalcSpec'));
    }

    /**
     * 事業所算定情報（介保・訪問介護）を更新する.
     *
     * @param int $officeId
     * @param int $id
     * @param \UseCase\Office\EditHomeVisitLongTermCareCalcSpecUseCase $useCase
     * @param \App\Http\Requests\UpdateHomeVisitLongTermCareCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $officeId, int $id, EditHomeVisitLongTermCareCalcSpecUseCase $useCase, UpdateHomeVisitLongTermCareCalcSpecRequest $request): HttpResponse
    {
        return JsonResponse::ok($useCase->handle($request->context(), $officeId, $id, $request->payload()));
    }
}
