<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffPasswordResetRequest;
use App\Http\Requests\Request;
use App\Http\Requests\StaffPasswordResetRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Staff\CreateStaffPasswordResetUseCase;
use UseCase\Staff\GetStaffPasswordResetUseCase;
use UseCase\Staff\ResetStaffPasswordUseCase;

/**
 * スタッフパスワード再設定コントローラー.
 */
final class StaffPasswordResetController extends Controller
{
    /**
     * スタッフパスワード再設定を取得する.
     *
     * @param string $token
     * @param \UseCase\Staff\GetStaffPasswordResetUseCase $useCase
     * @param \App\Http\Requests\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(string $token, GetStaffPasswordResetUseCase $useCase, Request $request): HttpResponse
    {
        $passwordReset = $useCase->handle($request->context(), $token);
        return JsonResponse::ok($passwordReset);
    }

    /**
     * スタッフパスワード再設定を登録する.
     *
     * @param \UseCase\Staff\CreateStaffPasswordResetUseCase $useCase
     * @param \App\Http\Requests\CreateStaffPasswordResetRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        CreateStaffPasswordResetUseCase $useCase,
        CreateStaffPasswordResetRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $request->email);
        return Response::created();
    }

    /**
     * スタッフのパスワードを再設定する.
     *
     * @param string $token
     * @param \UseCase\Staff\ResetStaffPasswordUseCase $useCase
     * @param \App\Http\Requests\StaffPasswordResetRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        string $token,
        ResetStaffPasswordUseCase $useCase,
        StaffPasswordResetRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $token, $request->password);
        return Response::noContent();
    }
}
