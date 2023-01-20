<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkCancelAttendanceRequest;
use App\Http\Requests\CancelAttendanceRequest;
use App\Http\Requests\ConfirmAttendanceRequest;
use App\Http\Requests\CreateAttendanceRequest;
use App\Http\Requests\FindAttendanceRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\CancelAttendanceJob;
use App\Jobs\ConfirmAttendanceJob;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\Shift\CancelAttendanceUseCase;
use UseCase\Shift\CreateAttendanceUseCase;
use UseCase\Shift\EditAttendanceUseCase;
use UseCase\Shift\FindAttendanceUseCase;
use UseCase\Shift\LookupAttendanceUseCase;

/**
 * 勤務実績コントローラー.
 */
class AttendanceController extends Controller
{
    /**
     * 勤務実績を一括キャンセルする.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\BulkCancelAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkCancel(CreateJobUseCase $useCase, BulkCancelAttendanceRequest $request): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $request): void {
            $this->dispatch(new CancelAttendanceJob($context, $domainJob, $request->reason, ...$request->ids));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 勤務実績をキャンセルする.
     *
     * @param int $id
     * @param \UseCase\Shift\CancelAttendanceUseCase $useCase
     * @param \App\Http\Requests\CancelAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancel(int $id, CancelAttendanceUseCase $useCase, CancelAttendanceRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->reason, $id);
        return Response::noContent();
    }

    /**
     * 勤務実績を登録する.
     *
     * @param \UseCase\Shift\CreateAttendanceUseCase $useCase
     * @param \App\Http\Requests\CreateAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateAttendanceUseCase $useCase, CreateAttendanceRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * 勤務実績を取得する.
     *
     * @param int $id
     * @param \UseCase\Shift\LookupAttendanceUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, LookupAttendanceUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $attendance = $useCase->handle(
            $request->context(),
            Permission::viewAttendances(),
            $id
        )->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Attendance({$id}) not found");
        });
        return JsonResponse::ok(compact('attendance'));
    }

    /**
     * 勤務実績を更新する.
     *
     * @param int $id
     * @param \UseCase\Shift\EditAttendanceUseCase $useCase
     * @param \App\Http\Requests\UpdateAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditAttendanceUseCase $useCase, UpdateAttendanceRequest $request): HttpResponse
    {
        $attendance = $useCase->handle($request->context(), $id, $request->payload());
        return Response::ok(compact('attendance'));
    }

    /**
     * 勤務実績の一覧取得をする.
     *
     * @param \UseCase\Shift\FindAttendanceUseCase $useCase
     * @param \App\Http\Requests\FindAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindAttendanceUseCase $useCase, FindAttendanceRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listAttendances(),
            $request->filterParams(['start' => 'scheduleDateAfter', 'end' => 'scheduleDateBefore']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 勤務実績を一括確定する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\ConfirmAttendanceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(CreateJobUseCase $useCase, ConfirmAttendanceRequest $request): HttpResponse
    {
        $context = $request->context();
        $ids = $request->ids;
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $ids): void {
            $this->dispatch(new ConfirmAttendanceJob($context, $domainJob, $ids));
        });
        return JsonResponse::accepted(compact('job'));
    }
}
