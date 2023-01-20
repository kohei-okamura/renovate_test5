<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateWithdrawalTransactionFileRequest;
use App\Http\Requests\CreateWithdrawalTransactionRequest;
use App\Http\Requests\FindWithdrawalTransactionRequest;
use App\Http\Requests\ImportWithdrawalTransactionFileRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\CreateWithdrawalTransactionFileJob;
use App\Jobs\CreateWithdrawalTransactionJob;
use App\Jobs\ImportWithdrawalTransactionFileJob;
use Domain\File\FileInputStream;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\Job\CreateJobWithFileUseCase;
use UseCase\UserBilling\FindWithdrawalTransactionUseCase;

/**
 * 口座振替データコントローラー.
 */
final class WithdrawalTransactionController extends Controller
{
    /**
     * 口座振替データを検索する.
     *
     * @param \UseCase\UserBilling\FindWithdrawalTransactionUseCase $useCase
     * @param \App\Http\Requests\FindWithdrawalTransactionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindWithdrawalTransactionUseCase $useCase, FindWithdrawalTransactionRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle($request->context(), Permission::listWithdrawalTransactions(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 口座振替データを作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateWithdrawalTransactionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateJobUseCase $useCase, CreateWithdrawalTransactionRequest $request): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $request): void {
            $this->dispatch(new CreateWithdrawalTransactionJob($context, $domainJob, $request->userBillingIds));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 全銀ファイルを作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateWithdrawalTransactionFileRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createFile(CreateJobUseCase $useCase, CreateWithdrawalTransactionFileRequest $request): HttpResponse
    {
        $context = $request->context();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $request): void {
            $this->dispatch(new CreateWithdrawalTransactionFileJob($context, $domainJob, $request->id));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 全銀ファイルをアップロードする.
     *
     * @param \UseCase\Job\CreateJobWithFileUseCase $useCase
     * @param \App\Http\Requests\ImportWithdrawalTransactionFileRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function import(CreateJobWithFileUseCase $useCase, ImportWithdrawalTransactionFileRequest $request): HttpResponse
    {
        $context = $request->context();
        $file = $request->file('file');
        $job = $useCase->handle($context, FileInputStream::fromFile($file), function (Job $domainJob, string $path) use ($context): void {
            $this->dispatch(new ImportWithdrawalTransactionFileJob($context, $path, $domainJob));
        });
        return JsonResponse::accepted(compact('job'));
    }
}
