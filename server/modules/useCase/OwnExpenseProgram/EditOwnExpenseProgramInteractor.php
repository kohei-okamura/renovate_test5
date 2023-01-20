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
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 自費サービス情報編集ユースケース実装.
 */
final class EditOwnExpenseProgramInteractor implements EditOwnExpenseProgramUseCase
{
    use Logging;

    private LookupOwnExpenseProgramUseCase $lookupUseCase;
    private OwnExpenseProgramRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase $lookupUseCase
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgramRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupOwnExpenseProgramUseCase $lookupUseCase,
        OwnExpenseProgramRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): OwnExpenseProgram
    {
        $x = $this->transaction->run(function () use ($context, $id, $values): OwnExpenseProgram {
            $entity = $this->lookupUseCase->handle($context, Permission::updateOwnExpensePrograms(), $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("OwnExpenseProgram({$id}) not found");
                });
            return $this->repository->store(
                $entity->copy(
                    $values + [
                        'version' => $entity->version + 1,
                        'updatedAt' => Carbon::now(),
                    ]
                )
            );
        });
        $this->logger()->info(
            '自費サービス情報が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
