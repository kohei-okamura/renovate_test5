<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\FindStaffDistanceRequest;
use App\Http\Requests\FindStaffRequest;
use App\Http\Requests\Request;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Permission\Permission;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Staff\CreateStaffWithInvitationUseCase;
use UseCase\Staff\EditStaffUseCase;
use UseCase\Staff\FindStaffDistanceUseCase;
use UseCase\Staff\FindStaffUseCase;
use UseCase\Staff\GetStaffInfoUseCase;
use UseCase\Staff\VerifyStaffEmailUseCase;

/**
 * スタッフコントローラー.
 */
final class StaffController extends Controller
{
    /**
     * スタッフを登録する.
     *
     * @param \UseCase\Staff\CreateStaffWithInvitationUseCase $useCase
     * @param \App\Http\Requests\CreateStaffRequest $request
     * @throws \Throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateStaffWithInvitationUseCase $useCase, CreateStaffRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->invitationId, $request->payload());
        return Response::created();
    }

    /**
     * スタッフ情報を取得する.
     *
     * @param int $id
     * @param \UseCase\Staff\GetStaffInfoUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, GetStaffInfoUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $staffInfo = $useCase->handle($request->context(), $id);
        return JsonResponse::ok($staffInfo);
    }

    /**
     * スタッフを更新する.
     *
     * @param int $id
     * @param \UseCase\Staff\EditStaffUseCase $useCase
     * @param \App\Http\Requests\UpdateStaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditStaffUseCase $useCase, UpdateStaffRequest $request): HttpResponse
    {
        $staffInfo = $useCase->handle($request->context(), $id, $request->payload());
        return Response::ok($staffInfo);
    }

    /**
     * スタッフを検索する.
     *
     * @param \UseCase\Staff\FindStaffUseCase $useCase
     * @param \App\Http\Requests\FindStaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindStaffUseCase $useCase, FindStaffRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listStaffs(),
            $request->filterParams(['status' => 'statuses']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 周辺にいるスタッフを検索する.
     *
     * @param \UseCase\Staff\FindStaffDistanceUseCase $useCase
     * @param \App\Http\Requests\FindStaffDistanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function distances(FindStaffDistanceUseCase $useCase, FindStaffDistanceRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listStaffs(),
            $request->filterParams(),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * スタッフのメールアドレスを検証する.
     *
     * @param string $token
     * @param \UseCase\Staff\VerifyStaffEmailUseCase $useCase
     * @param \App\Http\Requests\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verify(string $token, VerifyStaffEmailUseCase $useCase, Request $request): HttpResponse
    {
        $useCase->handle($request->context(), $token);
        return Response::ok();
    }
}
