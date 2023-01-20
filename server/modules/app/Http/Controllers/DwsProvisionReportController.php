<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDwsServiceReportPreviewRequest;
use App\Http\Requests\DeleteDwsProvisionReportRequest;
use App\Http\Requests\FindDwsProvisionReportRequest;
use App\Http\Requests\GetDwsProvisionReportTimeSummaryRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProvisionReportRequest;
use App\Http\Requests\UpdateDwsProvisionReportStatusRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\CreateDwsServiceReportPreviewJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\ProvisionReport\DeleteDwsProvisionReportUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;
use UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestUseCase;
use UseCase\ProvisionReport\UpdateDwsProvisionReportStatusUseCase;
use UseCase\ProvisionReport\UpdateDwsProvisionReportUseCase;

/**
 * 障害福祉サービス：予実コントローラー.
 */
final class DwsProvisionReportController extends Controller
{
    /**
     * サービス提供実績記録票（プレビュー版）を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateDwsServiceReportPreviewRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createPreview(CreateJobUseCase $useCase, CreateDwsServiceReportPreviewRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(
                new CreateDwsServiceReportPreviewJob(
                    $context,
                    $domainJob,
                    $payload['officeId'],
                    $payload['userId'],
                    $payload['providedIn']
                )
            );
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 障害福祉サービス：予実を削除する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\DeleteDwsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\DeleteDwsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(int $officeId, int $userId, string $providedIn, DeleteDwsProvisionReportUseCase $useCase, DeleteDwsProvisionReportRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $officeId, $userId, Carbon::parse($providedIn));
        return Response::noContent();
    }

    /**
     * 障害福祉サービス：予実を取得する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\GetDwsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $officeId, int $userId, string $providedIn, GetDwsProvisionReportUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $dwsProvisionReportOption = $useCase->handle(
            $request->context(),
            Permission::updateDwsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn),
        );

        foreach ($dwsProvisionReportOption as $dwsProvisionReport) {
            return JsonResponse::ok(compact('dwsProvisionReport'));
        }

        return JsonResponse::noContent();
    }

    /**
     * 介護保険サービス：予実状況の一覧取得をする.
     *
     * @param \UseCase\ProvisionReport\GetIndexDwsProvisionReportDigestUseCase $useCase
     * @param \App\Http\Requests\FindDwsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(GetIndexDwsProvisionReportDigestUseCase $useCase, FindDwsProvisionReportRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 障害福祉サービス：予実を更新（なければ登録）する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\UpdateDwsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\UpdateDwsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $officeId,
        int $userId,
        string $providedIn,
        UpdateDwsProvisionReportUseCase $useCase,
        UpdateDwsProvisionReportRequest $request
    ): HttpResponse {
        $dwsProvisionReport = $useCase->handle(
            $request->context(),
            $officeId,
            $userId,
            $providedIn,
            $request->payload()
        );
        return JsonResponse::ok(compact('dwsProvisionReport'));
    }

    /**
     * 障害福祉サービス：予実 状態を更新する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\UpdateDwsProvisionReportStatusUseCase $useCase
     * @param \App\Http\Requests\UpdateDwsProvisionReportStatusRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $officeId,
        int $userId,
        string $providedIn,
        UpdateDwsProvisionReportStatusUseCase $useCase,
        UpdateDwsProvisionReportStatusRequest $request
    ): HttpResponse {
        $dwsProvisionReport = $useCase->handle(
            $request->context(),
            $officeId,
            $userId,
            $providedIn,
            $request->payload()
        );
        return JsonResponse::ok(compact('dwsProvisionReport'));
    }

    /**
     * 障害福祉サービス：予実の合計単位数を取得する.
     *
     * @param \UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase $useCase
     * @param \App\Http\Requests\GetDwsProvisionReportTimeSummaryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTimeSummary(
        GetDwsProvisionReportTimeSummaryUseCase $useCase,
        GetDwsProvisionReportTimeSummaryRequest $request
    ): HttpResponse {
        $payload = $request->payload();
        $result = $useCase->handle(
            $request->context(),
            $payload['officeId'],
            $payload['userId'],
            $payload['providedIn'],
            Seq::fromArray($payload['plans']),
            Seq::fromArray($payload['results'])
        );
        return JsonResponse::ok($result);
    }
}
