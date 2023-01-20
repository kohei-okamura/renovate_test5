<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserLtcsCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserLtcsCalcSpecRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\User\CreateUserLtcsCalcSpecUseCase;
use UseCase\User\EditUserLtcsCalcSpecUseCase;
use UseCase\User\LookupUserLtcsCalcSpecUseCase;

/**
 * 介護保険サービス：利用者別算定情報コントローラー.
 */
final class UserLtcsCalcSpecController extends Controller
{
    /**
     * 介護保険サービス：利用者別算定情報を登録する.
     *
     * @param int $userId
     * @param \UseCase\User\CreateUserLtcsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\CreateUserLtcsCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $userId,
        CreateUserLtcsCalcSpecUseCase $useCase,
        CreateUserLtcsCalcSpecRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 介護保険サービス：利用者別算定情報を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\LookupUserLtcsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupUserLtcsCalcSpecUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $ltcsCalcSpec = $useCase->handle($request->context(), Permission::updateUserLtcsCalcSpecs(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserLtcsCalcSpec({$id}) not found");
            });
        return JsonResponse::ok(compact('ltcsCalcSpec'));
    }

    /**
     * 介護保険サービス：利用者別算定情報を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\EditUserLtcsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\UpdateUserLtcsCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        int $id,
        EditUserLtcsCalcSpecUseCase $useCase,
        UpdateUserLtcsCalcSpecRequest $request
    ): HttpResponse {
        $ltcsCalcSpec = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return Response::ok(compact('ltcsCalcSpec'));
    }
}
