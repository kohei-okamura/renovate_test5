<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserDwsCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserDwsCalcSpecRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\User\CreateUserDwsCalcSpecUseCase;
use UseCase\User\EditUserDwsCalcSpecUseCase;
use UseCase\User\LookupUserDwsCalcSpecUseCase;

/**
 * 障害福祉サービス：利用者別算定情報コントローラー.
 */
final class UserDwsCalcSpecController extends Controller
{
    /**
     * 障害福祉サービス：利用者別算定情報を登録する.
     *
     * @param int $userId
     * @param \UseCase\User\CreateUserDwsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\CreateUserDwsCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $userId,
        CreateUserDwsCalcSpecUseCase $useCase,
        CreateUserDwsCalcSpecRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 障害福祉サービス：利用者別算定情報を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\LookupUserDwsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupUserDwsCalcSpecUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $dwsCalcSpec = $useCase->handle($request->context(), Permission::updateUserDwsCalcSpecs(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserDwsCalcSpec({$id}) not found");
            });
        return JsonResponse::ok(compact('dwsCalcSpec'));
    }

    /**
     * 障害福祉サービス：利用者別算定情報を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\EditUserDwsCalcSpecUseCase $useCase
     * @param \App\Http\Requests\UpdateUserDwsCalcSpecRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        int $id,
        EditUserDwsCalcSpecUseCase $useCase,
        UpdateUserDwsCalcSpecRequest $request
    ): HttpResponse {
        $dwsCalcSpec = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return Response::ok(compact('dwsCalcSpec'));
    }
}
