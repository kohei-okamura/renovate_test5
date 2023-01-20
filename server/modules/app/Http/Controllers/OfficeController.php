<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOfficeRequest;
use App\Http\Requests\FindOfficeRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\EditOfficeLocationJob;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\CreateOfficeUseCase;
use UseCase\Office\EditOfficeUseCase;
use UseCase\Office\GetIndexOfficeUseCase;
use UseCase\Office\GetOfficeInfoUseCase;

/**
 * 事業所コントローラー.
 */
final class OfficeController extends Controller
{
    /**
     * 事業所を登録する.
     *
     * @param \UseCase\Office\CreateOfficeUseCase $useCase
     * @param \App\Http\Requests\CreateOfficeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateOfficeUseCase $useCase, CreateOfficeRequest $request): HttpResponse
    {
        $context = $request->context();
        $useCase->handle($context, $request->payload(), function (Office $office) use ($context) {
            $this->dispatch(new EditOfficeLocationJob($context, $office));
        });
        return Response::created();
    }

    /**
     * 事業所を取得する.
     *
     * @param int $id
     * @param \UseCase\Office\GetOfficeInfoUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, GetOfficeInfoUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $entity = $useCase->handle($request->context(), $id);
        return JsonResponse::ok($entity);
    }

    /**
     * 事業所を検索する.
     *
     * @param \UseCase\Office\GetIndexOfficeUseCase $useCase
     * @param \App\Http\Requests\FindOfficeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(GetIndexOfficeUseCase $useCase, FindOfficeRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            [Permission::listInternalOffices(), Permission::listExternalOffices()],
            $request->filterParams(['status' => 'statuses']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 事業所を更新する.
     *
     * @param int $id
     * @param \UseCase\Office\EditOfficeUseCase $useCase
     * @param \App\Http\Requests\UpdateOfficeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditOfficeUseCase $useCase, UpdateOfficeRequest $request): HttpResponse
    {
        $context = $request->context();
        $office = $useCase->handle($context, $id, $request->payload(), function (Office $office) use ($context) {
            $this->dispatch(new EditOfficeLocationJob($context, $office));
        });
        return Response::ok(compact('office'));
    }
}
