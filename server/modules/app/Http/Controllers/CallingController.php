<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Calling\AcknowledgeStaffAttendanceUseCase;
use UseCase\Calling\GetShiftsByTokenUseCase;

/**
 * 出勤確認コントローラー.
 */
class CallingController extends Controller
{
    /**
     * 出勤確認を登録する.
     *
     * @param string $token
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Calling\AcknowledgeStaffAttendanceUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acknowledges(
        string $token,
        StaffRequest $request,
        AcknowledgeStaffAttendanceUseCase $useCase
    ): HttpResponse {
        $useCase->handle($request->context(), $token);
        return Response::noContent();
    }

    /**
     * トークンに紐づく勤務シフトを取得する.
     *
     * @param string $token
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Calling\GetShiftsByTokenUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shifts(string $token, StaffRequest $request, GetShiftsByTokenUseCase $useCase): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $token);
        return JsonResponse::ok($finderResult);
    }
}
