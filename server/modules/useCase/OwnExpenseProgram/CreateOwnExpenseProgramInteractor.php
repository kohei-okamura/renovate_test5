<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\OwnExpenseProgram;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\OwnExpenseProgram\OwnExpenseProgramRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 自費サービス情報登録ユースケース実装.
 */
final class CreateOwnExpenseProgramInteractor implements CreateOwnExpenseProgramUseCase
{
    use Logging;

    private OwnExpenseProgramRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgramRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        OwnExpenseProgramRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    public function handle(Context $context, OwnExpenseProgram $ownExpenseProgram): OwnExpenseProgram
    {
        $x = $this->transaction->run(fn (): OwnExpenseProgram => $this->repository->store(
            $ownExpenseProgram->copy([
                'organizationId' => $context->organization->id,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ])
        ));
        $this->logger()->info(
            '自費サービス情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
