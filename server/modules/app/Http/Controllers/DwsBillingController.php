<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CopyDwsBillingRequest;
use App\Http\Requests\CreateDwsBillingRequest;
use App\Http\Requests\FindDwsBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatusRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\CopyDwsBillingJob;
use App\Jobs\CreateDwsBillingJob;
use App\Jobs\UpdateDwsBillingFilesJob;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\FindDwsBillingUseCase;
use UseCase\Billing\GetDwsBillingInfoUseCase;
use UseCase\Billing\UpdateDwsBillingStatusUseCase;
use UseCase\Job\CreateJobUseCase;

/**
 * 障害福祉サービス：請求 コントローラ.
 */
class DwsBillingController extends Controller
{
    /**
     * 障害福祉サービス：請求を生成する.
     *
     * @param \App\Http\Requests\CreateDwsBillingRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateDwsBillingRequest $request, CreateJobUseCase $useCase): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (DomainJob $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateDwsBillingJob(
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
     * 障害福祉サービス：請求 を取得する.
     *
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetDwsBillingInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, StaffRequest $request, GetDwsBillingInfoUseCase $useCase): HttpResponse
    {
        $context = $request->context();

        $responses = $useCase->handle($context, $id);

        return JsonResponse::ok($responses);
    }

    /**
     * 障害福祉サービス：請求 を検索する.
     *
     * @param \UseCase\Billing\FindDwsBillingUseCase $useCase
     * @param \App\Http\Requests\FindDwsBillingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindDwsBillingUseCase $useCase, FindDwsBillingRequest $request): HttpResponse
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
     * 障害福祉サービス：請求 状態を更新する.
     *
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingStatusRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $id,
        UpdateDwsBillingStatusRequest $request,
        UpdateDwsBillingStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $id,
            $payload,
            function (DomainJob $domainJob) use ($context, $id) {
                $this->dispatch(new UpdateDwsBillingFilesJob($context, $domainJob, $id));
            }
        );

        if (array_key_exists('job', $response)) {
            return JsonResponse::accepted($response);
        } else {
            return JsonResponse::ok($response);
        }
    }

    /**
     * 障害福祉サービス：請求をコピーして生成する.
     *
     * @param int $id
     * @param \App\Http\Requests\CopyDwsBillingRequest $request
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copy(int $id, CopyDwsBillingRequest $request, CreateJobUseCase $useCase): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (DomainJob $domainJob) use ($context, $id): void {
            $this->dispatch(new CopyDwsBillingJob(
                $context,
                $domainJob,
                $id
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }
}
