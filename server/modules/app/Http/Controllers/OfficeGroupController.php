<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateOfficeGroupRequest;
use App\Http\Requests\CreateOfficeGroupRequest;
use App\Http\Requests\DeleteOfficeGroupRequest;
use App\Http\Requests\FindOfficeGroupRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOfficeGroupRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\BulkEditOfficeGroupUseCase;
use UseCase\Office\CreateOfficeGroupUseCase;
use UseCase\Office\DeleteOfficeGroupUseCase;
use UseCase\Office\EditOfficeGroupUseCase;
use UseCase\Office\FindOfficeGroupUseCase;
use UseCase\Office\LookupOfficeGroupUseCase;

/**
 * 事業所グループコントローラー.
 */
final class OfficeGroupController extends Controller
{
    /**
     * 事業所グループを一括更新する.
     *
     * @param \UseCase\Office\BulkEditOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\BulkUpdateOfficeGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkUpdate(BulkEditOfficeGroupUseCase $useCase, BulkUpdateOfficeGroupRequest $request)
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::noContent();
    }

    /**
     * 事業所グループを登録する.
     *
     * @param \UseCase\Office\CreateOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\CreateOfficeGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateOfficeGroupUseCase $useCase, CreateOfficeGroupRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * 事業所グループを更新する.
     *
     * @param int $id
     * @param \UseCase\Office\EditOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\UpdateOfficeGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditOfficeGroupUseCase $useCase, UpdateOfficeGroupRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $id, $request->payload());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 事業所グループの一覧取得をする.
     *
     * @param \UseCase\Office\FindOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\FindOfficeGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindOfficeGroupUseCase $useCase, FindOfficeGroupRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 事業所グループを取得する.
     *
     * @param int $id
     * @param \UseCase\Office\LookupOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, LookupOfficeGroupUseCase $useCase, StaffRequest $request)
    {
        $officeGroup = $useCase->handle($request->context(), $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("OfficeGroup({$id}) not found");
        });
        return JsonResponse::ok(compact('officeGroup'));
    }

    /**
     * 事業所グループを削除する.
     *
     * @param int $id
     * @param \UseCase\Office\DeleteOfficeGroupUseCase $useCase
     * @param \App\Http\Requests\DeleteOfficeGroupRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(int $id, DeleteOfficeGroupUseCase $useCase, DeleteOfficeGroupRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $id);
        return Response::noContent();
    }
}
