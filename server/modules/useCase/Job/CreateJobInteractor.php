<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\JobRepository;
use Domain\Job\JobStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\UnauthorizedException;
use Lib\Logging;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * ジョブ登録実装.
 */
final class CreateJobInteractor implements CreateJobUseCase
{
    use Logging;
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

    /** {@inheritdoc} */
    public function handle(Context $context, callable $f): Job
    {
        /** @var \Domain\Staff\Staff $staff */
        $staff = $context->staff->getOrElse(function (): void {
            throw new UnauthorizedException();
        });
        $job = Job::create([
            'organizationId' => $context->organization->id,
            'staffId' => $staff->id,
            'status' => JobStatus::waiting(),
            'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        $x = $this->transaction->run(function () use ($job, $f): Job {
            $entity = $this->jobRepository->store($job->copy());
            $f($entity);
            return $entity;
        });
        $this->logger()->info(
            'ジョブが登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        return $this->jobRepository->lookupOptionByToken($token)->isEmpty();
    }
}
