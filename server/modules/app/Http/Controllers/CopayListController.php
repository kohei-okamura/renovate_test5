<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateCopayListRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\CreateCopayListJob;
use Domain\Job\Job;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;

/**
 * 利用者負担額一覧表コントローラー.
 */
final class CopayListController extends Controller
{
    /**
     * 利用者負担額一覧表を作成する.
     *
     * @param int $billingId
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateCopayListRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $billingId,
        CreateCopayListRequest $request,
        CreateJobUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $billingId, $request): void {
            $this->dispatch(new CreateCopayListJob(
                $context,
                $domainJob,
                $billingId,
                $request->ids,
                $request->isDivided
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }
}
