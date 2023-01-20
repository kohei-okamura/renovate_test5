<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDwsCertificationRequest;
use App\Http\Requests\DeleteDwsCertificationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsCertificationRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\DwsCertification\CreateDwsCertificationUseCase;
use UseCase\DwsCertification\DeleteDwsCertificationUseCase;
use UseCase\DwsCertification\EditDwsCertificationUseCase;
use UseCase\DwsCertification\LookupDwsCertificationUseCase;

/**
 * 障害福祉サービス受給者証コントローラー.
 */
final class DwsCertificationController extends Controller
{
    /**
     * 障害福祉サービス受給者証を登録する.
     *
     * @param int $userId
     * @param \UseCase\DwsCertification\CreateDwsCertificationUseCase $useCase
     * @param \App\Http\Requests\CreateDwsCertificationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $userId,
        CreateDwsCertificationUseCase $useCase,
        CreateDwsCertificationRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 障害福祉サービス受給者証を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\DwsCertification\LookupDwsCertificationUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupDwsCertificationUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $dwsCertification = $useCase->handle($request->context(), Permission::viewDwsCertifications(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsCertification({$id}) not found");
            });
        return JsonResponse::ok(compact('dwsCertification'));
    }

    /**
     * 障害福祉サービス受給者証を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\DwsCertification\EditDwsCertificationUseCase $useCase
     * @param \App\Http\Requests\UpdateDwsCertificationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        int $id,
        EditDwsCertificationUseCase $useCase,
        UpdateDwsCertificationRequest $request
    ): HttpResponse {
        $dwsCertification = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return Response::ok(compact('dwsCertification'));
    }

    /**
     * 障害福祉サービス受給者証を削除する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\DwsCertification\DeleteDwsCertificationUseCase $useCase
     * @param \App\Http\Requests\DeleteDwsCertificationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        int $userId,
        int $id,
        DeleteDwsCertificationUseCase $useCase,
        DeleteDwsCertificationRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $id);
        return Response::noContent();
    }
}
