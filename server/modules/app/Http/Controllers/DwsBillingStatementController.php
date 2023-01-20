<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateDwsBillingStatementStatusRequest;
use App\Http\Requests\RefreshDwsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationStatusRequest;
use App\Http\Requests\UpdateDwsBillingStatementRequest;
use App\Http\Requests\UpdateDwsBillingStatementStatusRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\BulkUpdateDwsBillingStatementStatusJob;
use App\Jobs\RefreshDwsBillingStatementJob;
use Domain\Job\Job;
use ScalikePHP\Option;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\EditDwsBillingStatementStatusUseCase;
use UseCase\Billing\GetDwsBillingStatementInfoUseCase;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationStatusUseCase;
use UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase;
use UseCase\Billing\UpdateDwsBillingStatementUseCase;
use UseCase\Job\CreateJobUseCase;

/**
 * 障害福祉サービス：明細書 コントローラ.
 */
class DwsBillingStatementController extends Controller
{
    /**
     * 障害福祉サービス：明細書 状態を一括更新する.
     *
     * @param int $billingId
     * @param \App\Http\Requests\BulkUpdateDwsBillingStatementStatusRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkStatus(
        int $billingId,
        BulkUpdateDwsBillingStatementStatusRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle(
            $context,
            function (Job $domainJob) use ($context, $billingId, $payload): void {
                $this->dispatch(new BulkUpdateDwsBillingStatementStatusJob(
                    $context,
                    $domainJob,
                    $billingId,
                    $payload['ids'],
                    $payload['status']
                ));
            }
        );

        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 障害福祉サービス：明細書：上限管理結果 を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copayCoordination(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateDwsBillingStatementCopayCoordinationRequest $request,
        UpdateDwsBillingStatementCopayCoordinationUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle($context, $billingId, $billingBundleId, $id, Option::some($payload));

        return JsonResponse::ok($response);
    }

    /**
     * 障害福祉サービス：明細書：上限管理区分 を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationStatusRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingStatementCopayCoordinationStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copayCoordinationStatus(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateDwsBillingStatementCopayCoordinationStatusRequest $request,
        UpdateDwsBillingStatementCopayCoordinationStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $copayCoordinationStatus = $request->payload();

        $response = $useCase->handle(
            $context,
            $billingId,
            $billingBundleId,
            $id,
            $copayCoordinationStatus
        );

        return JsonResponse::ok($response);
    }

    /**
     * 最新の予実を参照して障害福祉サービス：明細書等を更新（再生成）する.
     *
     * @param int $billingId
     * @param \App\Http\Requests\RefreshDwsBillingStatementRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refresh(
        int $billingId,
        RefreshDwsBillingStatementRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $billingId, $request): void {
            $this->dispatch(new RefreshDwsBillingStatementJob(
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
     * @param \UseCase\Billing\GetDwsBillingStatementInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $billingId,
        int $billingBundleId,
        int $id,
        StaffRequest $request,
        GetDwsBillingStatementInfoUseCase $useCase
    ): HttpResponse {
        $context = $request->context();

        $responses = $useCase->handle($context, $billingId, $billingBundleId, $id);

        return JsonResponse::ok($responses);
    }

    /**
     * 障害福祉サービス：明細書 状態を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingStatementStatusRequest $request
     * @param \UseCase\Billing\EditDwsBillingStatementStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateDwsBillingStatementStatusRequest $request,
        EditDwsBillingStatementStatusUseCase $useCase
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
     * 障害福祉サービス：明細書 を更新する.
     *
     * @param int $billingId
     * @param int $billingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingStatementRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingStatementUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $billingId,
        int $billingBundleId,
        int $id,
        UpdateDwsBillingStatementRequest $request,
        UpdateDwsBillingStatementUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle($context, $billingId, $billingBundleId, $id, $payload);

        return JsonResponse::ok($response);
    }
}
