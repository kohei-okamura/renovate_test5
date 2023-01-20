<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateDwsBillingServiceReportStatusRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingServiceReportStatusRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\BulkUpdateDwsBillingServiceReportStatusJob;
use Domain\Job\Job;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\GetDwsBillingServiceReportInfoUseCase;
use UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase;
use UseCase\Job\CreateJobUseCase;

/**
 * 障害福祉サービス サービス提供実績記録票コントローラー.
 */
class DwsBillingServiceReportController extends Controller
{
    /**
     * 障害福祉サービス：サービス提供実績記録票 を取得する.
     *
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetDwsBillingServiceReportInfoUseCase $useCase
     * @return HttpResponse
     */
    public function get(
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        StaffRequest $request,
        GetDwsBillingServiceReportInfoUseCase $useCase
    ): HttpResponse {
        $context = $request->context();

        $responses = $useCase->handle($context, $dwsBillingId, $dwsBillingBundleId, $id);

        return JsonResponse::ok($responses);
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票 状態を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingServiceReportStatusRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateDwsBillingServiceReportStatusRequest $request,
        UpdateDwsBillingServiceReportStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $billingId,
            $billingBundleId,
            $id,
            $payload
        );

        return JsonResponse::ok($response);
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票 状態を一括更新する.
     *
     * @param int $billingId
     * @param \App\Http\Requests\BulkUpdateDwsBillingServiceReportStatusRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkStatus(
        int $billingId,
        BulkUpdateDwsBillingServiceReportStatusRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $billingId, $payload): void {
            $this->dispatch(new BulkUpdateDwsBillingServiceReportStatusJob(
                $context,
                $domainJob,
                $billingId,
                $payload['ids'],
                $payload['status']
            ));
        });

        return JsonResponse::accepted(compact('job'));
    }
}
