<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\FindUserRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\EditUserLocationJob;
use Domain\Permission\Permission;
use Domain\User\User;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\User\CreateUserUseCase;
use UseCase\User\EditUserUseCase;
use UseCase\User\FindUserUseCase;
use UseCase\User\GetUserInfoUseCase;

/**
 * 利用者コントローラー.
 */
final class UserController extends Controller
{
    /**
     * 利用者を登録する.
     *
     * @param \UseCase\User\CreateUserUseCase $useCase
     * @param \App\Http\Requests\CreateUserRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateUserUseCase $useCase, CreateUserRequest $request): HttpResponse
    {
        $context = $request->context();
        $useCase->handle($context, $request->payload(), function (User $user) use ($context) {
            $this->dispatch(new EditUserLocationJob($context, $user));
        });
        return Response::created();
    }

    /**
     * 利用者情報を取得する.
     *
     * @param int $id
     * @param \UseCase\User\GetUserInfoUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, GetUserInfoUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $userInfo = $useCase->handle($request->context(), $id);
        return JsonResponse::ok($userInfo);
    }

    /**
     * 利用者を検索する.
     *
     * @param \UseCase\User\FindUserUseCase $useCase
     * @param \App\Http\Requests\FindUserRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindUserUseCase $useCase, FindUserRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), Permission::listUsers(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 利用者を更新する.
     *
     * @param int $id
     * @param \UseCase\User\EditUserUseCase $useCase
     * @param \App\Http\Requests\UpdateUserRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditUserUseCase $useCase, UpdateUserRequest $request): HttpResponse
    {
        $context = $request->context();
        $user = $useCase->handle($request->context(), $id, $request->payload(), function (User $user) use ($context) {
            $this->dispatch(new EditUserLocationJob($context, $user));
        });
        return Response::ok(compact('user'));
    }
}
