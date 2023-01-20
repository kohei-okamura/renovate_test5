<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserBillingInvoiceRequest;
use App\Http\Requests\CreateUserBillingNoticeRequest;
use App\Http\Requests\CreateUserBillingReceiptRequest;
use App\Http\Requests\CreateUserBillingStatementRequest;
use App\Http\Requests\DeleteUserBillingDepositRequest;
use App\Http\Requests\FindUserBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserBillingDepositRequest;
use App\Http\Requests\UpdateUserBillingRequest;
use App\Http\Response\JsonResponse;
use App\Jobs\CreateUserBillingInvoiceJob;
use App\Jobs\CreateUserBillingNoticeJob;
use App\Jobs\CreateUserBillingReceiptJob;
use App\Jobs\CreateUserBillingStatementJob;
use App\Jobs\DeleteUserBillingDepositJob;
use App\Jobs\UpdateUserBillingDepositJob;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\UserBilling\FindUserBillingUseCase;
use UseCase\UserBilling\LookupUserBillingUseCase;
use UseCase\UserBilling\UpdateUserBillingUseCase;

/**
 * 利用者請求コントローラー.
 */
final class UserBillingController extends Controller
{
    /**
     * 利用者請求を検索する.
     *
     * @param \UseCase\UserBilling\FindUserBillingUseCase $useCase
     * @param \App\Http\Requests\FindUserBillingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(FindUserBillingUseCase $useCase, FindUserBillingRequest $request): HttpResponse
    {
        $finderResult = $useCase->handle(
            $request->context(),
            Permission::listUserBillings(),
            $request->filterParams(),
            $request->paginationParams()
        );
        return JsonResponse::ok($finderResult);
    }

    /**
     * 利用者請求を取得する.
     *
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $id, StaffRequest $request, LookupUserBillingUseCase $useCase): HttpResponse
    {
        $context = $request->context();

        $userBilling = $useCase->handle($context, Permission::viewUserBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserBilling({$id}) not found.");
            });
        return JsonResponse::ok(compact('userBilling'));
    }

    /**
     * 利用者請求を更新する.
     *
     * @param int $id
     * @param \UseCase\UserBilling\UpdateUserBillingUseCase $useCase
     * @param \App\Http\Requests\UpdateUserBillingRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $id, UpdateUserBillingUseCase $useCase, UpdateUserBillingRequest $request): HttpResponse
    {
        $userBilling = $useCase->handle($request->context(), $id, $request->payload());
        return JsonResponse::ok(compact('userBilling'));
    }

    /**
     * 利用者請求入金日を更新する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\UpdateUserBillingDepositRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateDeposit(CreateJobUseCase $useCase, UpdateUserBillingDepositRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new UpdateUserBillingDepositJob(
                $context,
                $domainJob,
                $payload['depositedAt'],
                $payload['ids']
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 利用者請求入金日を削除する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\DeleteUserBillingDepositRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteDeposit(CreateJobUseCase $useCase, DeleteUserBillingDepositRequest $request): HttpResponse
    {
        $context = $request->context();
        $ids = $request->ids;
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $ids): void {
            $this->dispatch(new DeleteUserBillingDepositJob($context, $domainJob, ...$ids));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 利用者請求：請求書を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateUserBillingInvoiceRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createInvoice(CreateJobUseCase $useCase, CreateUserBillingInvoiceRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateUserBillingInvoiceJob(
                $context,
                $domainJob,
                $payload['ids'],
                $payload['issuedOn']
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 利用者請求：領収書を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateUserBillingReceiptRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReceipt(CreateJobUseCase $useCase, CreateUserBillingReceiptRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateUserBillingReceiptJob(
                $context,
                $domainJob,
                $payload['ids'],
                $payload['issuedOn']
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 代理受領額通知書を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateUserBillingNoticeRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createNotice(CreateJobUseCase $useCase, CreateUserBillingNoticeRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateUserBillingNoticeJob(
                $context,
                $domainJob,
                $payload['ids'],
                $payload['issuedOn']
            ));
        });
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * 介護サービス利用明細書を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateUserBillingStatementRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createStatement(CreateJobUseCase $useCase, CreateUserBillingStatementRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(new CreateUserBillingStatementJob($context, $domainJob, $payload['ids'], $payload['issuedOn']));
        });
        return JsonResponse::accepted(compact('job'));
    }
}
