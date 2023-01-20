<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\JobRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * ジョブ編集実装.
 */
final class EditJobInteractor implements EditJobUseCase
{
    use Logging;

    private LookupJobUseCase $lookupUseCase;
    private JobRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\LookupJobUseCase $lookupUseCase
     * @param \Domain\Job\JobRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupJobUseCase $lookupUseCase,
        JobRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): Job
    {
        $entity = $this->lookupUseCase->handle($context, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Entity({$id}) not found");
        });
        $x = $this->transaction->run(fn (): Job => $this->repository->store(
            $entity->copy($values + ['updatedAt' => Carbon::now()])
        ));
        $this->logger()->info(
            'ジョブが更新されました',
            ['id' => $x->id, 'status' => $x->status->value()] + $context->logContext()
        );
        return $x;
    }
}
