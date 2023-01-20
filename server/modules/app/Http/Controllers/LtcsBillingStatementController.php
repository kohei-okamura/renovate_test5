<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateLtcsBillingStatementStatusRequest;
use App\Http\Requests\RefreshLtcsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsBillingStatementRequest;
use App\Http\Requests\UpdateLtcsBillingStatementStatusRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\BulkUpdateLtcsBillingStatementStatusJob;
use App\Jobs\ConfirmLtcsBillingStatementStatusJob;
use App\Jobs\RefreshLtcsBillingStatementJob;
use Domain\Billing\LtcsBilling;
use Domain\Job\Job;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\GetLtcsBillingStatementInfoUseCase;
use UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase;
use UseCase\Billing\UpdateLtcsBillingStatementUseCase;
use UseCase\Job\CreateJobUseCase;

/**
 * 介護保険サービス：明細書 コントローラ.
 */
final class LtcsBillingStatementController extends Controller
{
    /**
     * 介護保険サービス：明細書 状態を一括更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param \App\Http\Requests\BulkUpdateLtcsBillingStatementStatusRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkStatus(
        int $billingId,
        int $billingBundleId,
        BulkUpdateLtcsBillingStatementStatusRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $billingId, $billingBundleId, $payload): void {
            $this->dispatch(new BulkUpdateLtcsBillingStatementStatusJob(
                $context,
                $domainJob,
                $billingId,
                $billingBundleId,
                $payload['ids'],
                $payload['status']
            ));
        });

        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 最新の予実を参照して介護保険サービス：明細書を更新（再生成）する.
     *
     * @param int $billingId
     * @param \App\Http\Requests\RefreshLtcsBillingStatementRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refresh(
        int $billingId,
        RefreshLtcsBillingStatementRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $billingId, $request): void {
            $this->dispatch(new RefreshLtcsBillingStatementJob(
                $context,
                $domainJob,
                $billingId,
                $request->ids,
            ));
        });

        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 障害福祉サービス：明細書 を取得する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetLtcsBillingStatementInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $billingId,
        int $billingBundleId,
        int $id,
        StaffRequest $request,
        GetLtcsBillingStatementInfoUseCase $useCase
    ): HttpResponse {
        $context = $request->context();

        $responses = $useCase->handle($context, $billingId, $billingBundleId, $id);

        return JsonResponse::ok($responses);
    }

    /**
     * 介護保険サービス：明細書 状態を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateLtcsBillingStatementStatusRequest $request
     * @param \UseCase\Billing\UpdateLtcsBillingStatementStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateLtcsBillingStatementStatusRequest $request,
        UpdateLtcsBillingStatementStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $billingId,
            $billingBundleId,
            $id,
            $payload,
            function (LtcsBilling $billing) use ($context) {
                $this->dispatch(new ConfirmLtcsBillingStatementStatusJob($context, $billing));
            }
        );

        return JsonResponse::ok($response);
    }

    /**
     * 介護保険サービス：明細書 を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateLtcsBillingStatementRequest $request
     * @param \UseCase\Billing\UpdateLtcsBillingStatementUseCase $useCase
     * @throws \Throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateLtcsBillingStatementRequest $request,
        UpdateLtcsBillingStatementUseCase $useCase
    ): HttpResponse {
        $response = $useCase->handle(
            $request->context(),
            $billingId,
            $billingBundleId,
            $id,
            $request->payload()
        );

        return JsonResponse::ok($response);
    }
}
