<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLtcsBillingRequest;
use App\Http\Requests\FindLtcsBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsBillingStatusRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\CreateLtcsBillingJob;
use App\Jobs\UpdateLtcsBillingFilesJob;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\FindLtcsBillingUseCase;
use UseCase\Billing\GetLtcsBillingInfoUseCase;
use UseCase\Billing\UpdateLtcsBillingStatusUseCase;
use UseCase\Job\CreateJobUseCase;

/**
 * 介護保険サービス：請求 コントローラ.
 */
class LtcsBillingController extends Controller
{
    /**
     * 介護保険サービス：請求を生成する.
     *
     * @param \App\Http\Requests\CreateLtcsBillingRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateLtcsBillingRequest $request, CreateJobUseCase $useCase): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateLtcsBillingJob(
                $context,
                $domainJob,
                $payload['officeId'],
                $payload['transactedIn'],
                $payload['fixedAt'],
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 介護保険サービス：請求 を取得する.
     *
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetLtcsBillingInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, StaffRequest $request, GetLtcsBillingInfoUseCase $useCase): HttpResponse
    {
        $context = $request->context();

        $response = $useCase->handle($context, $id);

        return JsonResponse::ok($response);
    }

    /**
     * 介護保険サービス：請求 を検索する.
     *
     * @param \UseCase\Billing\FindLtcsBillingUseCase $useCase
     * @param \App\Http\Requests\FindLtcsBillingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindLtcsBillingUseCase $useCase, FindLtcsBillingRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listBillings(),
            $request->filterParams(['start' => 'transactedInAfter', 'end' => 'transactedInBefore']),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 介護保険サービス：請求 状態を更新する.
     *
     * @param int $id
     * @param \App\Http\Requests\UpdateLtcsBillingStatusRequest $request
     * @param \UseCase\Billing\UpdateLtcsBillingStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $id,
        UpdateLtcsBillingStatusRequest $request,
        UpdateLtcsBillingStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $id,
            $payload,
            function (Job $domainJob) use ($context, $id) {
                $this->dispatch(new UpdateLtcsBillingFilesJob($context, $domainJob, $id));
            }
        );

        if (array_key_exists('job', $response)) {
            return JsonResponse::accepted($response);
        } else {
            return JsonResponse::ok($response);
        }
    }
}
