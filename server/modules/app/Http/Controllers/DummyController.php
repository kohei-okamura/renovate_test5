<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Common\Carbon;
use Domain\Job\Job as DomainJob;
use Domain\Job\JobRepository;
use Domain\Job\JobStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * ダミーコントローラー.
 */
final class DummyController extends Controller
{
    use UniqueTokenSupport;

    private const MAX_RETRY_COUNT = 100;
    private const TOKEN_LENGTH = 60;

    private JobRepository $jobRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Job\JobRepository $jobRepository
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        JobRepository $jobRepository,
        TokenMaker $tokenMaker,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->jobRepository = $jobRepository;
        $this->tokenMaker = $tokenMaker;
        $this->transaction = $transactionManagerFactory->factory($jobRepository);
    }

    public function createFile(StaffRequest $request): HttpResponse
    {
        $context = $request->context();
        /** @var \Domain\Staff\Staff $staff */
        $staff = $context->staff->getOrElse(function (): void {
            throw new UnauthorizedException();
        });

        $job = $this->transaction->run(fn (): DomainJob => $this->jobRepository->store(DomainJob::create([
            'organizationId' => $context->organization->id,
            'staffId' => $staff->id,
            'status' => JobStatus::success(),
            'data' => [
                'uri' => $context->uri('dummies/download/dummy'),
                'filename' => 'dummy.pdf',
            ],
            'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ])));
        return JsonResponse::accepted(compact('job'));
    }

    /**
     * ダミーファイルをダウンロードする.
     *
     * @param string $path
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(string $path, StaffRequest $request): HttpResponse
    {
        return Response::ok(fopen(resource_path($path . '.pdf'), 'rb'), ['Content-Type' => 'application/pdf']);
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        return true;
    }
}
