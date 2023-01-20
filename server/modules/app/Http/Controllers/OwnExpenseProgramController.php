<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOwnExpenseProgramRequest;
use App\Http\Requests\FindOwnExpenseProgramRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOwnExpenseProgramRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\OwnExpenseProgram\CreateOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\EditOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\FindOwnExpenseProgramUseCase;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;

/**
 * 自費サービス情報コントローラー.
 */
final class OwnExpenseProgramController extends Controller
{
    /**
     * 自費サービス情報を登録する.
     *
     * @param \UseCase\OwnExpenseProgram\CreateOwnExpenseProgramUseCase $useCase
     * @param \App\Http\Requests\CreateOwnExpenseProgramRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateOwnExpenseProgramUseCase $useCase, CreateOwnExpenseProgramRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * 自費サービス情報を取得する.
     *
     * @param int $id
     * @param \UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, LookupOwnExpenseProgramUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $ownExpenseProgram = $useCase->handle($request->context(), Permission::viewOwnExpensePrograms(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("OwnExpenseProgram({$id}) not found");
            });
        return JsonResponse::ok(compact('ownExpenseProgram'));
    }

    /**
     * 自費サービス情報の一覧取得をする.
     *
     * @param \UseCase\OwnExpenseProgram\FindOwnExpenseProgramUseCase $useCase
     * @param \App\Http\Requests\FindOwnExpenseProgramRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindOwnExpenseProgramUseCase $useCase, FindOwnExpenseProgramRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listOwnExpensePrograms(),
            $request->filterParams(['officeId' => 'officeIdOrNull']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 自費サービス情報を更新する.
     *
     * @param int $id
     * @param \UseCase\OwnExpenseProgram\EditOwnExpenseProgramUseCase $useCase
     * @param \App\Http\Requests\UpdateOwnExpenseProgramRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditOwnExpenseProgramUseCase $useCase, UpdateOwnExpenseProgramRequest $request): HttpResponse
    {
        $ownExpenseProgram = $useCase->handle($request->context(), $id, $request->payload());
        return JsonResponse::ok(compact('ownExpenseProgram'));
    }
}
