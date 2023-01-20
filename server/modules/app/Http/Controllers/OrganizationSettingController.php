<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrganizationSettingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOrganizationSettingRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Organization\CreateOrganizationSettingUseCase;
use UseCase\Organization\EditOrganizationSettingUseCase;
use UseCase\Organization\LookupOrganizationSettingUseCase;

/**
 * 事業者別設定コントローラー.
 */
final class OrganizationSettingController extends Controller
{
    /**
     * 事業者別設定を取得する.
     *
     * @param \UseCase\Organization\LookupOrganizationSettingUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(LookupOrganizationSettingUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $organizationSetting = $useCase->handle($request->context(), Permission::viewOrganizationSettings())
            ->getOrElse(function (): void {
                throw new NotFoundException('OrganizationSetting not found');
            });
        return JsonResponse::ok(compact('organizationSetting'));
    }

    /**
     * 事業者別設定を登録する.
     *
     * @param \UseCase\Organization\CreateOrganizationSettingUseCase $useCase
     * @param \App\Http\Requests\CreateOrganizationSettingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateOrganizationSettingUseCase $useCase, CreateOrganizationSettingRequest $request): HttpResponse
    {
        $context = $request->context();
        $useCase->handle($context, $request->payload());
        return Response::created();
    }

    /**
     * 事業者別設定を更新する.
     *
     * @param \UseCase\Organization\EditOrganizationSettingUseCase $useCase
     * @param \App\Http\Requests\UpdateOrganizationSettingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(EditOrganizationSettingUseCase $useCase, UpdateOrganizationSettingRequest $request): HttpResponse
    {
        $organizationSetting = $useCase->handle($request->context(), $request->payload());
        return JsonResponse::ok(compact('organizationSetting'));
    }
}
