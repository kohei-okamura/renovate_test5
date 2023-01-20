<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\DeleteRoleRequest;
use App\Http\Requests\FindRoleRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Role\CreateRoleUseCase;
use UseCase\Role\DeleteRoleUseCase;
use UseCase\Role\EditRoleUseCase;
use UseCase\Role\FindRoleUseCase;
use UseCase\Role\LookupRoleUseCase;

/**
 * ロールコントローラー.
 */
final class RoleController extends Controller
{
    /**
     * ロールを登録する.
     *
     * @param \UseCase\Role\CreateRoleUseCase $useCase
     * @param \App\Http\Requests\CreateRoleRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateRoleUseCase $useCase, CreateRoleRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * ロールを取得する.
     *
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Role\LookupRoleUseCase $useCase
     * @return \Illuminate\Http\Response
     */
    public function get(int $id, StaffRequest $request, LookupRoleUseCase $useCase): HttpResponse
    {
        $role = $useCase->handle($request->context(), $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Role({$id}) not found");
        });
        return JsonResponse::ok(compact('role'));
    }

    /**
     * ロールを更新する.
     *
     * @param int $id
     * @param \UseCase\Role\EditRoleUseCase $useCase
     * @param \App\Http\Requests\UpdateRoleRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditRoleUseCase $useCase, UpdateRoleRequest $request): HttpResponse
    {
        $role = $useCase->handle($request->context(), $id, $request->payload());
        return JsonResponse::ok(compact('role'));
    }

    /**
     * ロールの一覧取得をする.
     *
     * @param \UseCase\Role\FindRoleUseCase $useCase
     * @param \App\Http\Requests\FindRoleRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindRoleUseCase $useCase, FindRoleRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * ロールを削除する.
     *
     * @param int $id
     * @param \UseCase\Role\DeleteRoleUseCase $useCase
     * @param \App\Http\Requests\DeleteRoleRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(int $id, DeleteRoleUseCase $useCase, DeleteRoleRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $id);
        return Response::noContent();
    }
}
