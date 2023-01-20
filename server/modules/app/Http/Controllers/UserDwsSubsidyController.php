<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserDwsSubsidyRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserDwsSubsidyRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\User\CreateUserDwsSubsidyUseCase;
use UseCase\User\EditUserDwsSubsidyUseCase;
use UseCase\User\LookupUserDwsSubsidyUseCase;

/**
 * 自治体助成情報コントローラー.
 */
final class UserDwsSubsidyController extends Controller
{
    /**
     * 自治体助成情報を登録する.
     *
     * @param int $userId
     * @param \UseCase\User\CreateUserDwsSubsidyUseCase $useCase
     * @param \App\Http\Requests\CreateUserDwsSubsidyRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(int $userId, CreateUserDwsSubsidyUseCase $useCase, CreateUserDwsSubsidyRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 自治体助成情報を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\LookupUserDwsSubsidyUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupUserDwsSubsidyUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $dwsSubsidy = $useCase->handle($request->context(), Permission::viewUserDwsSubsidies(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserDwsSubsidy({$id}) not found");
            });
        return JsonResponse::ok(compact('dwsSubsidy'));
    }

    /**
     * 自治体助成情報を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\EditUserDwsSubsidyUseCase $useCase
     * @param \App\Http\Requests\UpdateUserDwsSubsidyRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $userId, int $id, EditUserDwsSubsidyUseCase $useCase, UpdateUserDwsSubsidyRequest $request): HttpResponse
    {
        $dwsSubsidy = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return JsonResponse::ok(compact('dwsSubsidy'));
    }
}
