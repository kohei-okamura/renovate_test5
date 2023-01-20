<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkCancelShiftRequest;
use App\Http\Requests\CancelShiftRequest;
use App\Http\Requests\ConfirmShiftRequest;
use App\Http\Requests\CreateShiftRequest;
use App\Http\Requests\CreateShiftTemplateRequest;
use App\Http\Requests\FindShiftRequest;
use App\Http\Requests\ImportShiftRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\CancelShiftJob;
use App\Jobs\ConfirmShiftJob;
use App\Jobs\CreateShiftTemplateJob;
use App\Jobs\ImportShiftJob;
use Domain\File\FileInputStream;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\Job\CreateJobWithFileUseCase;
use UseCase\Shift\CancelShiftUseCase;
use UseCase\Shift\CreateShiftUseCase;
use UseCase\Shift\EditShiftUseCase;
use UseCase\Shift\FindShiftUseCase;
use UseCase\Shift\LookupShiftUseCase;

/**
 * 勤務シフトコントローラー.
 */
class ShiftController extends Controller
{
    /**
     * 勤務シフトを一括キャンセルする.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\BulkCancelShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkCancel(CreateJobUseCase $useCase, BulkCancelShiftRequest $request): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $request): void {
            $this->dispatch(new CancelShiftJob($context, $domainJob, $request->reason, ...$request->ids));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 勤務シフトをキャンセルする.
     *
     * @param int $id
     * @param \UseCase\Shift\CancelShiftUseCase $useCase
     * @param \App\Http\Requests\CancelShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancel(int $id, CancelShiftUseCase $useCase, CancelShiftRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->reason, $id);
        return Response::noContent();
    }

    /**
     * 勤務シフトを登録する.
     *
     * @param \UseCase\Shift\CreateShiftUseCase $useCase
     * @param \App\Http\Requests\CreateShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateShiftUseCase $useCase, CreateShiftRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * 勤務シフトを取得する.
     *
     * @param int $id
     * @param \UseCase\Shift\LookupShiftUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, LookupShiftUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $shift = $useCase->handle(
            $request->context(),
            Permission::viewShifts(),
            $id
        )->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Shift({$id}) not found");
        });
        return JsonResponse::ok(compact('shift'));
    }

    /**
     * 勤務シフトを更新する.
     *
     * @param int $id
     * @param \UseCase\Shift\EditShiftUseCase $useCase
     * @param \App\Http\Requests\UpdateShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, EditShiftUseCase $useCase, UpdateShiftRequest $request): HttpResponse
    {
        $shift = $useCase->handle($request->context(), $id, $request->payload());
        return Response::ok(compact('shift'));
    }

    /**
     * 勤務シフトの一覧取得をする.
     *
     * @param \UseCase\Shift\FindShiftUseCase $useCase
     * @param \App\Http\Requests\FindShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindShiftUseCase $useCase, FindShiftRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listShifts(),
            $request->filterParams(['start' => 'scheduleDateAfter', 'end' => 'scheduleDateBefore']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 勤務シフトを確定する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\ConfirmShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(CreateJobUseCase $useCase, ConfirmShiftRequest $request): HttpResponse
    {
        $context = $request->context();
        $ids = $request->ids;
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $ids): void {
            $this->dispatch(new ConfirmShiftJob($context, $domainJob, $ids));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 勤務シフト雛形を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateShiftTemplateRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createTemplate(CreateJobUseCase $useCase, CreateShiftTemplateRequest $request): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $request): void {
            $this->dispatch(new CreateShiftTemplateJob($context, $domainJob, $request->payload()));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 勤務シフト雛形から勤務シフトを一括登録する.
     *
     * @param \UseCase\Job\CreateJobWithFileUseCase $useCase
     * @param \App\Http\Requests\ImportShiftRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function import(CreateJobWithFileUseCase $useCase, ImportShiftRequest $request): HttpResponse
    {
        $context = $request->context();
        $file = $request->file('file');
        $job = $useCase->handle(
            $context,
            FileInputStream::fromFile($file),
            function (Job $domainJob, string $path) use ($context): void {
                $this->dispatch(new ImportShiftJob($context, $path, $domainJob));
            }
        );
        return JsonResponse::accepted(compact('job'));
    }
}
