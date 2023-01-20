<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDwsContractRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsContractRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Contract\CreateContractUseCase;
use UseCase\Contract\EditContractUseCase;
use UseCase\Contract\LookupContractUseCase;

/**
 * 障害福祉サービス契約コントローラ.
 */
final class DwsContractController extends Controller
{
    /**
     * 契約を登録する.
     *
     * @param int $userId
     * @param \UseCase\Contract\CreateContractUseCase $useCase
     * @param \App\Http\Requests\CreateDwsContractRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(int $userId, CreateContractUseCase $useCase, CreateDwsContractRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 契約を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Contract\LookupContractUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupContractUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $contract = $useCase->handle($request->context(), Permission::viewDwsContracts(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Contract({$id}) not found");
            });
        return JsonResponse::ok(compact('contract'));
    }

    /**
     * 契約を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Contract\EditContractUseCase $useCase
     * @param \App\Http\Requests\UpdateDwsContractRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        int $id,
        EditContractUseCase $useCase,
        UpdateDwsContractRequest $request
    ): HttpResponse {
        $contract = $useCase->handle(
            $request->context(),
            Permission::updateDwsContracts(),
            $userId,
            $id,
            $request->payload()
        );
        return JsonResponse::ok(compact('contract'));
    }
}
