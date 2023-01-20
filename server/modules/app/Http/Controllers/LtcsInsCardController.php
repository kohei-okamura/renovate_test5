<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLtcsInsCardRequest;
use App\Http\Requests\DeleteLtcsInsCardRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsInsCardRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\LtcsInsCard\CreateLtcsInsCardUseCase;
use UseCase\LtcsInsCard\DeleteLtcsInsCardUseCase;
use UseCase\LtcsInsCard\EditLtcsInsCardUseCase;
use UseCase\LtcsInsCard\LookupLtcsInsCardUseCase;

/**
 * 介護保険被保険者証コントローラー.
 */
final class LtcsInsCardController extends Controller
{
    /**
     * 介護保険被保険者証を登録する.
     *
     * @param int $userId
     * @param \UseCase\LtcsInsCard\CreateLtcsInsCardUseCase $useCase
     * @param \App\Http\Requests\CreateLtcsInsCardRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $userId,
        CreateLtcsInsCardUseCase $useCase,
        CreateLtcsInsCardRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 介護保険被保険者証を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\LtcsInsCard\LookupLtcsInsCardUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupLtcsInsCardUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $ltcsInsCard = $useCase->handle($request->context(), Permission::viewLtcsInsCards(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsInsCard[{$id}] not found");
            });
        return JsonResponse::ok(compact('ltcsInsCard'));
    }

    /**
     * 介護保険被保険者証を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\LtcsInsCard\EditLtcsInsCardUseCase $useCase
     * @param \App\Http\Requests\UpdateLtcsInsCardRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        int $id,
        EditLtcsInsCardUseCase $useCase,
        UpdateLtcsInsCardRequest $request
    ): HttpResponse {
        $ltcsInsCard = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return Response::ok(compact('ltcsInsCard'));
    }

    /**
     * 介護保険被保険者証を削除する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\LtcsInsCard\DeleteLtcsInsCardUseCase $useCase
     * @param \App\Http\Requests\DeleteLtcsInsCardRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        int $userId,
        int $id,
        DeleteLtcsInsCardUseCase $useCase,
        DeleteLtcsInsCardRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $id);
        return Response::noContent();
    }
}
