<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserLtcsSubsidyRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserLtcsSubsidyRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\User\CreateUserLtcsSubsidyUseCase;
use UseCase\User\DeleteUserLtcsSubsidyUseCase;
use UseCase\User\EditUserLtcsSubsidyUseCase;
use UseCase\User\LookupUserLtcsSubsidyUseCase;

/**
 * 公費情報コントローラー.
 */
final class UserLtcsSubsidyController extends Controller
{
    /**
     * 公費情報を登録する.
     *
     * @param int $userId
     * @param \UseCase\User\CreateUserLtcsSubsidyUseCase $useCase
     * @param \App\Http\Requests\CreateUserLtcsSubsidyRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(int $userId, CreateUserLtcsSubsidyUseCase $useCase, CreateUserLtcsSubsidyRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 公費情報を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\EditUserLtcsSubsidyUseCase $useCase
     * @param \App\Http\Requests\UpdateUserLtcsSubsidyRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $userId, int $id, EditUserLtcsSubsidyUseCase $useCase, UpdateUserLtcsSubsidyRequest $request): HttpResponse
    {
        $ltcsSubsidy = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return JsonResponse::ok(compact('ltcsSubsidy'));
    }

    /**
     * 公費情報を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\LookupUserLtcsSubsidyUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupUserLtcsSubsidyUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $ltcsSubsidy = $useCase->handle($request->context(), Permission::viewUserLtcsSubsidies(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserLtcsSubsidy({$id}) not found");
            });
        return JsonResponse::ok(compact('ltcsSubsidy'));
    }

    /**
     * 公費情報を削除する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\User\DeleteUserLtcsSubsidyUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(int $userId, int $id, DeleteUserLtcsSubsidyUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $userId, $id);
        return Response::noContent();
    }
}
